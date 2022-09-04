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
use Storage;

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

        $this->copyFilesToEfs();

        $this->zipDirectory();

        $this->moveZipFile();

        $this->createDownload($exportQueue);

        $exportQueue->stage = 6;
        $exportQueue->save();

        \Artisan::call('export:poll');

        ZooniverseExportCreateReportJob::dispatch($exportQueue);
    }

    /**
     * Copy exported files from s3 to efs.
     *
     * @return void
     * @throws \Exception
     */
    private function copyFilesToEfs()
    {
        $bucketPath = $this->bucketPath . '/' . $this->workingDir;
        $efsPath = Storage::disk('efs')->path($this->efsWorkDirFolder);
        exec("aws s3 cp $bucketPath $efsPath --recursive", $out, $ret);
        if ($ret !== 0) {
            throw new \Exception("Could not copy $bucketPath to $efsPath");
        }
    }

    /**
     * Create zip file from efs directory.
     *
     * @return void
     * @throws \Exception
     */
    private function zipDirectory()
    {
        $efsWorkDirFolder = Storage::disk('efs')->path($this->efsWorkDirFolder);
        $efsWorkDir = Storage::disk('efs')->path($this->efsWorkDir);

        exec("zip -r -j $efsWorkDir/{$this->exportArchiveFile} $efsWorkDirFolder" , $output, $retval);

        if ($retval !== 0) {
            throw new \Exception(t("Could not create zip file $efsWorkDir/{$this->exportArchiveFile} from $efsWorkDirFolder"));
        }
    }

    /**
     * Move zip file to s3 bucket.
     *
     * @return void
     * @throws \Exception
     */
    private function moveZipFile()
    {
        $bucketPathZip = $this->bucketPath . '/' . $this->exportDirectory . '/' . $this->exportArchiveFile;
        $efsZipFle = Storage::disk('efs')->path("{$this->efsWorkDir}/{$this->exportArchiveFile}");

        exec("aws s3 mv $efsZipFle $bucketPathZip", $out, $ret);
        if ($ret !== 0) {
            throw new \Exception("Could not copy $efsZipFle to $bucketPathZip");
        }
    }

    /**
     * Create download file.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     */
    private function createDownload(ExportQueue $exportQueue)
    {
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
    }
}