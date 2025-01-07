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

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
    protected $signature = 'app:deploy-files';

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
    private $storagePath;

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

        $this->resPath = base_path('resources/');
        $this->supPath = Storage::path('supervisord');
        $this->setAppsConfigs();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $supFiles = File::files($this->resPath.'files/supervisord');
        collect($supFiles)->map(function ($file) {
            $string = File::get($file);
            $this->apps->each(function ($search) use (&$string) {
                $replace = $this->configureReplace($search);
                $string = str_replace($search, $replace, $string);
            });

            $target = $this->supPath.'/'.File::name($file);
            File::put($target, $string);
        });
    }

    /**
     * @param $search
     * @return false|\Illuminate\Config\Repository|mixed|string
     */
    private function configureReplace($search)
    {
        if ($search === 'APP_URL' || $search === 'APP_ENV' || $search === 'APP_DOMAIN') {
            return config(str_replace('_', '.', strtolower($search)));
        }

        if (strpos($search, 'QUEUE_') === 0) {
            $replace = strtolower(str_replace('QUEUE_', '', $search));

            return config('config.'.$replace);
        }

        return config('config.'.strtolower($search));
    }

    /**
     * Set search and replace arrays.
     */
    private function setAppsConfigs()
    {
        $this->apps = collect([
            'APP_ENV',
            'APP_DOMAIN',
            'SERVER_USER',
            'CURRENT_PATH',
            'NUM_PROCS',
            'QUEUE_RAPID_TUBE'
        ]);
    }
}
