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

use App\Services\Transcriptions\CreatePusherClassificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to process Zooniverse classification data through Pusher.
 *
 * This job handles the processing of Zooniverse classification data by dispatching
 * it to the Pusher classification service. It runs on a dedicated queue for
 * classification processing.
 *
 * @implements ShouldQueue
 */
class ZooniverseClassificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param  array  $data  The classification data from Zooniverse to be processed
     * @param  string  $title  The title of the project or expedition
     * @return void
     */
    public function __construct(protected array $data, protected string $title)
    {
        $this->onQueue(config('config.queue.pusher_classification'));
    }

    /**
     * Execute the job.
     *
     * Processes the Zooniverse classification data using the Pusher classification service.
     * The service handles the creation and distribution of classification data through
     * the Pusher channels.
     *
     * @param  CreatePusherClassificationService  $createPusherClassificationService  Service to process classifications
     *
     * @throws \Exception If the classification processing fails
     */
    public function handle(CreatePusherClassificationService $createPusherClassificationService): void
    {
        $createPusherClassificationService->process($this->data, $this->title);
    }
}
