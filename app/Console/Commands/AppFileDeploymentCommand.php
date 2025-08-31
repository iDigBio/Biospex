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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class AppFileDeploymentCommand
 */
class AppFileDeploymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles moving, renaming, and replacing files needed per environment settings';

    private Collection $config;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->setConfig();

        $supFiles = File::files(base_path('resources').'/supervisor');
        $supTargets = collect($supFiles)->map(function ($file) {

            if (! Storage::exists('supervisor')) {
                Storage::makeDirectory('supervisor');
            }

            $target = Storage::path('supervisor').'/'.$file->getBaseName();
            if (File::exists($target)) {
                File::delete($target);
            }
            File::copy($file->getPathname(), $target);

            return $target;
        });

        $this->config->each(function ($search) use ($supTargets) {
            $replace = $this->configureReplace($search);
            $supTargets->each(function ($file) use ($search, $replace) {
                exec("sed -i 's*$search*$replace*g' $file");
            });
        });
    }

    /**
     * @return false|\Illuminate\Config\Repository|mixed|string
     */
    private function configureReplace($search): mixed
    {
        if (str_starts_with($search, 'APP_')) {
            if (str_ends_with($search, '_ENV')) {
                return config('app.env');
            }

            return config('config.'.strtolower($search));
        }

        if ($search === 'REVERB_DEBUG') {
            return config('config.reverb_debug') === 'true' ? '--debug' : '';
        }

        return config('config.'.strtolower(\Str::replaceFirst('_', '.', $search)));
    }

    /**
     * Set search and replace arrays.
     */
    private function setConfig(): void
    {
        $this->config = collect(config('config.deployment_fields'));
    }
}
