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

namespace App\Jobs;

use App\Models\ExportQueue;
use App\Repositories\ExportQueueFileRepository;
use App\Services\Actor\Zooniverse\Traits\ZooniverseErrorNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseExportCheckImageProcessJob
 */
class ZooniverseExportCheckImageProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * @var int
     */
    public int $timeout = 300;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
        $this->onQueue(config('config.queue.lambda_export'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @return void
     * @throws \Exception
     */
    public function handle(
        ExportQueueFileRepository $exportQueueFileRepository,
    )
    {
        $count = $exportQueueFileRepository->getUncompletedCount($this->exportQueue->id);

        if ($this->attempts() < 5 && $count !== 0) {
            $this->release(config('config.aws.lambda_export_delay'));
        }

        if ($count === 0) {
            $this->exportQueue->stage = 4;
            $this->exportQueue->save();

            ZooniverseExportBuildCsvJob::dispatch($this->exportQueue);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $throwable
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $this->sendErrorNotification($this->exportQueue, $throwable);
    }
}
