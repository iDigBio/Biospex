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

namespace App\Services\Actor\Zooniverse;

use App\Jobs\ZooniverseExportCreateReportJob;
use App\Models\Download;
use App\Models\ExportQueue;
use App\Services\Actor\ActorDirectory;
use Storage;

/**
 * Class ZooniverseBuildZip
 */
readonly class ZooniverseBuildZip
{
    /**
     * Construct.
     */
    public function __construct(private Download $download) {}

    /**
     * Process the actor.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue, ActorDirectory $actorDirectory)
    {
        $exportQueue->load(['expedition']);

        $actorDirectory->deleteS3File($actorDirectory->exportArchiveFilePath);

        $this->copyFilesToEfs($actorDirectory);

        $this->zipDirectory($actorDirectory);

        $this->moveZipFile($actorDirectory);

        $this->createDownload($exportQueue, $actorDirectory->exportArchiveFile);

        ZooniverseExportCreateReportJob::dispatch($exportQueue, $actorDirectory);
    }

    /**
     * Copy exported files from s3 to efs.
     *
     * @throws \Exception
     */
    private function copyFilesToEfs(ActorDirectory $actorDirectory): void
    {
        $bucketPath = $actorDirectory->bucketPath.'/'.$actorDirectory->workingDir;
        $efsPath = Storage::disk('efs')->path($actorDirectory->efsExportDirFolder);
        exec("aws s3 cp $bucketPath $efsPath --recursive", $out, $ret);
        if ($ret !== 0) {
            throw new \Exception("Could not copy $bucketPath to $efsPath");
        }
    }

    /**
     * Create zip file from efs directory.
     *
     * @throws \Exception
     */
    private function zipDirectory(ActorDirectory $actorDirectory): void
    {
        $efsExportDirFolder = Storage::disk('efs')->path($actorDirectory->efsExportDirFolder);
        $efsExportDir = Storage::disk('efs')->path($actorDirectory->efsExportDir);

        exec("zip -r -j $efsExportDir/{$actorDirectory->exportArchiveFile} $efsExportDirFolder", $output, $retval);

        if ($retval !== 0) {
            throw new \Exception(t("Could not create zip file $efsExportDir/{$actorDirectory->exportArchiveFile} from $efsExportDirFolder"));
        }
    }

    /**
     * Move zip file to s3 bucket.
     *
     * @throws \Exception
     */
    private function moveZipFile(ActorDirectory $actorDirectory): void
    {
        $bucketPathZip = $actorDirectory->bucketPath.'/'.$actorDirectory->exportDirectory.'/'.$actorDirectory->exportArchiveFile;
        $efsZipFle = Storage::disk('efs')->path("{$actorDirectory->efsExportDir}/{$actorDirectory->exportArchiveFile}");

        exec("aws s3 mv $efsZipFle $bucketPathZip", $out, $ret);
        if ($ret !== 0) {
            throw new \Exception("Could not copy $efsZipFle to $bucketPathZip");
        }
    }

    /**
     * Create download file.
     */
    private function createDownload(ExportQueue $exportQueue, string $exportArchiveFile): void
    {
        $values = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id' => $exportQueue->actor_id,
            'file' => $exportArchiveFile,
            'type' => 'export',
        ];
        $attributes = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id' => $exportQueue->actor_id,
            'file' => $exportArchiveFile,
            'type' => 'export',
        ];

        $this->download->updateOrCreate($attributes, $values);
    }
}
