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
use App\Services\Actor\NfnPanoptes\ZooniverseExportBuildImageRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class ZooniverseExportBuildImageRequestsJob
 */
class ZooniverseExportBuildImageRequestsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NfnErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    public ExportQueue $exportQueue;

    /**
     * @var int
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     */
    public function __construct(ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue;
        $this->onQueue(config('config.queues.export'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\NfnPanoptes\ZooniverseExportBuildImageRequests $zooniverseBuildRequests
     * @return void
     * @throws \Exception
     */
    public function handle(ZooniverseExportBuildImageRequests $zooniverseBuildRequests)
    {
        $zooniverseBuildRequests->process($this->exportQueue);
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
