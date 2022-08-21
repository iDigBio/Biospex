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

use App\Jobs\AppLambdaQueueJob;
use App\Jobs\TestJob;
use App\Models\Expedition;
use App\Services\Actor\NfnPanoptes\ZooniverseExportProcessImage;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * AppCommand constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {
        TestJob::dispatch();
        //AppLambdaQueueJob::dispatch();
        /*
        $basePath = base_path('imgProcess.js');
        $folder = \Storage::path('tmp');

        $url = "http://cdn.flmnh.ufl.edu/Herbarium/jpg/116/116667s1.jpg";
        $fileOne = "116667s1.jpg";
        $command = "node $basePath $fileOne $url $folder 1500 1500";
        */
        /*
        $url = "http://cdn.flmnh.ufl.edu/Herbarium/jpg/074/74718s1.jpg";
        $fileTwo = "74718s1.jpg";
        $command = "node $basePath $fileTwo $url $folder 1800 1800";
        */

        //exec($command, $output);
        //dd($output);
    }
}