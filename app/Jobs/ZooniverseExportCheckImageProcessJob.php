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
use App\Services\Actor\NfnPanoptes\Traits\NfnErrorNotification;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NfnErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
        $this->onQueue(config('config.export_tube'));
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
        $this->release(30);

        return;
        $this->exportQueue->stage = 3;
        $this->exportQueue->save();

        $count = $exportQueueFileRepository->getUncompletedCount($this->exportQueue->id);
        \Log::alert('Incomplete count = ' . $count);

        if ($count === 0) {
            \Log::alert('Complete');
            //ZooniverseExportBuildCsvJob::dispatch($this->exportQueue);
            $this->delete();
        }

        if ($this->attempts() < 4) {
            \Log::alert('attempt '.$this->attempts());
            //ZooniverseExportCheckImageProcessJob::dispatch($this->exportQueue)->delay(30);
            $this->release(30);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $this->sendErrorNotification($this->exportQueue, $exception);
    }
}
