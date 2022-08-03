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

namespace App\Console\Commands;

use App;
use File;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storage;

/**
 * Class AppFileDeployment
 *
 * @package App\Console\Commands
 */
class AppFileDeployment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles moving, renaming, and replacing files needed per environment settings';

    /**
     * @var string
     */
    private $resPath;

    /**
     * @var string
     */
    private $appPath;

    /**
     * @var string
     */
    private $supPath;

    /**
     * @var Collection
     */
    private $apps;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->resPath = base_path('resources');
        $this->appPath = base_path();
        $this->supPath = Storage::path('supervisord');
        $this->setAppsConfigs();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // copy needed files to locations
        $appFiles = File::files($this->resPath.'/apps');
        $appTargets = collect($appFiles)->map(function ($file) {
            $target = $this->appPath.'/'.File::name($file);
            File::copy($file, $target);

            return $target;
        });

        $supFiles = File::files($this->resPath.'/supervisord');
        $subTargets = collect($supFiles)->map(function ($file) {
            $target = $this->supPath.'/'.File::name($file);
            File::copy($file, $target);

            return $target;
        });

        $files = $appTargets->merge($subTargets);

        $this->apps->each(function ($search) use ($files) {
            $replace = $this->configureReplace($search);
            $files->each(function ($file) use ($search, $replace) {
                exec("sed -i 's*$search*$replace*g' $file");
            });
        });
    }

    /**
     * @param $search
     * @return false|\Illuminate\Config\Repository|mixed|string
     */
    private function configureReplace($search)
    {
        if ($search === 'APP_URL' || $search === 'APP_ENV') {
            return config(str_replace('_', '.', strtolower($search)));
        }

        if ($search === 'REDIS_HOST') {
            return config('database.redis.default.host');
        }

        if (strpos($search, 'QUEUE_') === 0) {
            $replace = strtolower(str_replace('QUEUE_', '', $search));

            return config('config.'.$replace);
        }

        if ($search === 'MAP_PRIVATE_KEY') {
            return json_encode(base64_decode(config('config.'.strtolower($search))));
        }

        return config('config.'.strtolower($search));
    }

    /**
     * Set search and replace arrays.
     */
    private function setAppsConfigs()
    {
        $this->apps = collect([
            'APP_URL',
            'APP_ENV',

            'SERVER_USER',
            'CURRENT_PATH',

            'REDIS_HOST',

            'API_URL',
            'API_VERSION',
            'API_TOKEN',

            'NUM_PROCS',
            'QUEUE_CHART_TUBE',
            'QUEUE_CLASSIFICATION_TUBE',
            'QUEUE_DEFAULT_TUBE',
            'QUEUE_EVENT_TUBE',
            'QUEUE_IMPORT_TUBE',
            'QUEUE_EXPORT_TUBE',
            'QUEUE_RECONCILE_TUBE',
            'QUEUE_STAT_TUBE',
            'QUEUE_WORKFLOW_TUBE',
            'QUEUE_OCR_TUBE',
            'QUEUE_PUSHER_TUBE',
            'QUEUE_PUSHER_PROCESS_TUBE',
            'QUEUE_WORKING_TUBE',
            'QUEUE_LAMBDA_TUBE'
        ]);
    }
}
