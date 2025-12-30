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
use Illuminate\Support\Facades\Queue;
use Pheanstalk\Values\TubeName;

class QueueCountCommand extends Command
{
    protected $signature = 'queue:count {queue? : The name of the queue to count}';

    protected $description = 'Count the number of jobs in a Beanstalkd queue';

    public function handle()
    {
        $queueName = $this->argument('queue') ?? config('queue.connections.beanstalkd.queue', 'default');
        $connection = Queue::connection('beanstalkd');
        $pheanstalk = $connection->getPheanstalk();
        $tube = new TubeName($queueName);
        $stats = $pheanstalk->statsTube($tube);
        $count = $stats->currentJobsReady; // Property access for Pheanstalk 7.x
        \Log::info("Queue '{$queueName}' has {$count} jobs.");

        return $count;
    }
}
