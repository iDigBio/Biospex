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
use Illuminate\Support\Facades\Queue;
use Pheanstalk\Pheanstalk;

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
     * @var \Pheanstalk\Pheanstalk
     */
    private Pheanstalk $pheanstalk;

    /**
     * AppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * peek, peakReady, peekBuried, peakDelayed
     */
    public function handle()
    {

        // "aws s3 mv s3://biospex-app/scratch/2-2-c5afceb7-b475-4628-8cdc-6fb2d0b939d5 /efs/batch/ --recursive"
    }

    public function clearTube($tube)
    {
        /*
        try
        {
            $pheanstalk = Queue::getPheanstalk();
            $pheanstalk->useTube('default');

            while($job = $pheanstalk->peekReady())
            {
                $pheanstalk->delete($job);
            }
        }
        catch(\Exception $e){}
        */
    }
}