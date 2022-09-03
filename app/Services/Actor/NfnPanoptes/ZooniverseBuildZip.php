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

namespace App\Services\Actor\NfnPanoptes;

use App\Jobs\ZooniverseExportCreateReportJob;
use App\Models\ExportQueue;
use App\Services\Actor\QueueInterface;
use App\Services\Actor\Traits\ActorDirectory;
use App\Repositories\DownloadRepository;
use App\Repositories\ExportQueueRepository;

/**
 * Class ZooniverseBuildZip
 */
class ZooniverseBuildZip implements QueueInterface
{
    use ActorDirectory;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepository;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\DownloadRepository $downloadRepository
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        DownloadRepository $downloadRepository
    )
    {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * Process the actor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue)
    {
        $exportQueue->load(['expedition']);

        $this->setFolder($exportQueue->id, $exportQueue->actor_id, $exportQueue->expedition->uuid);
        $this->setDirectories();

        $this->deleteFile($this->exportArchiveFilePath);

        $output=null;
        $retval=null;
        $path = config('config.current_path') . '/zipExport.js';

        $start_time = microtime(true);

        exec("node $path {$this->workingDir} {$this->exportArchiveFilePath}" , $output, $retval);

        // End clock time in seconds
        $end_time = microtime(true);

        // Calculate script execution time
        $execution_time = ($end_time - $start_time);

        \Log::alert(" Execution time of script = ".$execution_time." sec");

        if ($retval) {
            throw new \Exception($output);
        }

        $values = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id'      => $exportQueue->actor_id,
            'file'          => $this->exportArchiveFile,
            'type'          => 'export',
        ];
        $attributes = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id'      => $exportQueue->actor_id,
            'file'          => $this->exportArchiveFile,
            'type'          => 'export',
        ];

        $this->downloadRepository->updateOrCreate($attributes, $values);

        $this->cleanDirectory(config('config.aws_efs_lambda_dir'));

        $exportQueue->stage = 6;
        $exportQueue->save();

        \Artisan::call('export:poll');

        ZooniverseExportCreateReportJob::dispatch($exportQueue);
    }
}