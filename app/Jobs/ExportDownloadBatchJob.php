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

use App\Repositories\DownloadRepository;
use App\Services\Actor\NfnPanoptes\Traits\NfnErrorNotification;
use App\Services\Actor\NfnPanoptes\ZooniverseExportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Class ExportDownloadBatchJob
 *
 * @package App\Jobs
 */
class ExportDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, NfnErrorNotification;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 3600;

    /**
     * @var int
     */
    private int $downloadId;

    /**
     * ExportDownloadBatchJob constructor.
     *
     * @param int $downloadId
     */
    public function __construct(int $downloadId)
    {
        $this->downloadId = $downloadId;
        $this->onQueue(config('config.queues.export'));
    }

    /**
     * Handle download batch job.
     *
     * @param \App\Repositories\DownloadRepository $downloadRepository
     * @param \App\Services\Actor\NfnPanoptes\ZooniverseExportBatch $nfnPanoptesExportBatch
     */
    public function handle(
        DownloadRepository $downloadRepository,
        ZooniverseExportBatch $nfnPanoptesExportBatch)
    {
        $download = $downloadRepository->findWith($this->downloadId, ['expedition.project.group.owner', 'actor']);

        try {
            $nfnPanoptesExportBatch->process($download);
        }
        catch (Throwable $e) {
            $this->sendAdminError($e);
            $this->delete();
        }
    }
}