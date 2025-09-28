<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Providers;

use App\Services\DarwinCore\MetaFileProcessor;
use App\Services\Helpers\CountService;
use App\Services\Helpers\DateService;
use App\Services\Helpers\TranscriptionMapService;
use App\Services\Transcriptions\PanoptesTranscriptionService;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laracasts\Utilities\JavaScript\LaravelViewBinder;
use Laracasts\Utilities\JavaScript\Transformers\Transformer;
use View;

/**
 * Class BiospexServiceProvider
 */
class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setViewComposers();
    }

    public function register(): void
    {
        $this->registerFacades();

        Collection::macro('shuffleWords', function () {
            $keys = $this->keys()->shuffle();

            return $keys->map(function ($key) {
                return [$key, $this[$key]];
            });
        });

        $this->app->bind(Transformer::class, function ($app) {
            return new Transformer(
                new LaravelViewBinder(
                    $app['events'],
                    config('javascript.bind_js_vars_to_this_view')
                ),
                config('javascript.js_namespace')
            );
        });
    }

    /**
     * Set up view composers
     */
    public function setViewComposers(): void
    {
        View::composer('common.notices', 'App\Http\ViewComposers\NoticesComposer');
        View::composer(['common.process-modal', 'common.modal'], 'App\Http\ViewComposers\PhpVarsComposer');
        View::composer('common.nav', 'App\Http\ViewComposers\NavComposer');
    }

    /**
     * Registers custom facades
     */
    public function registerFacades(): void
    {
        $this->app->singleton('counthelper', function () {
            return new CountService(app(PanoptesTranscriptionService::class));
        });

        $this->app->singleton('datehelper', function () {
            return new DateService;
        });

        $this->app->singleton('transcriptionmaphelper', function () {
            return new TranscriptionMapService(
                $this->app['config']->get('zooniverse.reserved_encoded'),
                $this->app['config']->get('zooniverse.mapped_transcription_fields')
            );
        });

        $this->app->bind(MetaFileProcessor::class, function ($app) {
            return new MetaFileProcessor(
                $app->make(\App\Services\DarwinCore\DarwinCoreXmlLoader::class),
                $app->make(\App\Models\Meta::class),
                $app['config']->get('config.dwcRequiredRowTypes'),
                $app['config']->get('config.dwcRequiredFields')
            );
        });
    }
}
