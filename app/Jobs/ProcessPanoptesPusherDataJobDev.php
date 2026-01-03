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

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPanoptesPusherDataJobDev implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $data)
    {
        $this->onQueue(config('config.queue.pusher_process', 'default'));
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function handle(): void
    {
        // === FIX WSL2 FLOAT BUG ONLY ===
        if (isset($this->job)) {
            $pheanstalkJob = $this->job->getPheanstalkJob();
            if (method_exists($pheanstalkJob, 'stats')) {
                $stats = $pheanstalkJob->stats();
                $reflection = new \ReflectionClass($stats);

                foreach (['age', 'timeLeft'] as $prop) {
                    $property = $reflection->getProperty($prop);
                    @$property->setAccessible(true);
                    $property->setValue($stats, (int) $property->getValue($stats));
                }
            }
        }

        // === PASS RAW STRING TO ORIGINAL JOB ===
        (new ProcessPanoptesPusherDataJob($this->data))->handle();
    }
}
