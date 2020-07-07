<?php
/**
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

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\NfnPanoptesExportBatch;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 36000;

    /**
     * @var string
     */
    private $downloadId;

    /**
     * ExportDownloadBatchJob constructor.
     *
     * @param string $downloadId
     */
    public function __construct(string $downloadId)
    {
        $this->onQueue(config('config.export_tube'));
        $this->downloadId = $downloadId;
    }

    /**
     * Handle download batch job.
     *
     * @param \App\Services\Actor\NfnPanoptesExportBatch $nfnPanoptesExportBatch
     */
    public function handle(NfnPanoptesExportBatch $nfnPanoptesExportBatch)
    {
        $download = $nfnPanoptesExportBatch->getDownload($this->downloadId);

        try {
            $nfnPanoptesExportBatch->process($download);
        }
        catch (Exception $e) {
            $user = User::find(1);
            $message = [
                'Actor:' . $download->actor_id,
                'Expedition: ' . $download->expedition_id,
                'Message: ' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}