<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Providers;

use App\Repositories\PanoptesTranscriptionRepository;
use App\Services\Helpers\CountHelper;
use App\Services\Helpers\DateHelper;
use App\Services\Helpers\FlashHelper;
use App\Services\Helpers\GeneralHelper;
use App\Services\Helpers\TranscriptionMapHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * Class BiospexServiceProvider
 *
 * @package App\Providers
 */
class BiospexServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->setViewComposers();
    }

    public function register()
    {
        $this->registerFacades();

        Collection::macro('shuffleWords', function () {
            $keys = $this->keys()->shuffle();
            return $keys->map(function($key) {
                return [$key, $this[$key]];
            });
        });
    }

    /**
     * Set up view composers
     */
    public function setViewComposers()
    {
        view()->composer(
            'common.notices', 'App\Http\ViewComposers\NoticesComposer'
        );

        view()->composer(
            'common.process-modal', 'App\Http\ViewComposers\PollComposer'
        );
    }

    /**
     * Registers custom facades
     */
    public function registerFacades()
    {
        $this->app->singleton('flash', function ()
        {
            return new FlashHelper();
        });

        $this->app->singleton('datehelper', function(){
            return new DateHelper();
        });

        $this->app->singleton('generalhelper', function(){
            return new GeneralHelper();
        });

        $this->app->singleton('counthelper', function(){
            return new CountHelper(app(PanoptesTranscriptionRepository::class));
        });

        $this->app->singleton('transcriptionmaphelper', function() {
            return new TranscriptionMapHelper(
                $this->app['config']->get('config.reserved_encoded'),
                $this->app['config']->get('config.mapped_transcription_fields')
            );
        });
    }
}