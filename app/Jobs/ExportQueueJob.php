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
use App\Services\Actor\NfnPanoptes\Traits\NfnErrorNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ExportQueueJob
 *
 * @package App\Jobs
 */
class ExportQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NfnErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * @var int
     */
    public int $timeout = 300;

    /**
     * ExportQueueJob constructor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
        $this->onQueue(config('config.export_tube'));
    }

    /**
     * Handle ExportQueue Job
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        switch ($this->exportQueue->stage) {
            case 1:
                ZooniverseExportBuildImageRequestsJob::dispatch($this->exportQueue);
                break;
            case 2:
                ZooniverseExportLambdaJob::dispatch($this->exportQueue);
                break;
            case 3:
                ZooniverseExportCheckImageProcessJob::dispatch($this->exportQueue);
                break;
            case 4:
                ZooniverseExportBuildCsvJob::dispatch($this->exportQueue);
                break;
            case 5:
                ZooniverseExportBuildZipJob::dispatch($this->exportQueue);
                break;
            case 6:
                ZooniverseExportCreateReportJob::dispatch($this->exportQueue);
                break;
            case 7:
                ZooniverseExportDeleteFilesJob::dispatch($this->exportQueue);
                break;
            default:
                throw new Exception(t('Export Queue error. No stage value given'));
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
