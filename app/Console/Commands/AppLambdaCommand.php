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
use Illuminate\Console\Command;
use Queue;
use Aws\Lambda\LambdaClient;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppLambdaCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'lambda:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test sqs lambda code';

    /**
     * AppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function handle()
    {
        $data = [
            'id'  => 1234567,
            'url' => 'https://biospex.org/some/test.jpg',
        ];

        $client = new LambdaClient([
            'credentials' => [
                'key'    => config('queue.connections.sqs.key'),
                'secret' => config('queue.connections.sqs.secret'),
            ],
            'version'     => 'latest',
            'region'      => config('queue.connections.sqs.region'),
        ]);

        //$result = $client->invokeAsync([.....])
        $result = $client->invoke([
            // The name your created Lamda function
            'FunctionName' => 'ImageProcessing',
            'Payload'      => json_encode($data),
        ]);

        //print_r($result);
        print_r($result['Payload']->getContents());
        echo PHP_EOL;
    }
}