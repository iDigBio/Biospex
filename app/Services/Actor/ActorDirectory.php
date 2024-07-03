<?php
/*
 * Copyright (c) 2022. Biospex
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

namespace App\Services\Actor;

use Illuminate\Support\Facades\Storage;

/**
 * Trait ActorDirectory
 */
class ActorDirectory
{
    /**
     * @var string
     */
    public string $folderName;

    /**
     * @var string
     */
    public string $scratchDir;

    /**
     * @var string
     */
    public string $workingDir;

    /**
     * @var string
     */
    public string $exportDirectory;

    /**
     * @var string
     */
    public string $exportArchiveFile;

    /**
     * @var string
     */
    public string $exportArchiveFilePath;

    /**
     * @var array
     */
    public array $rejected  = [];

    /**
     * @var string
     */
    public string $expeditionUuid;

    /**
     * @var string
     */
    public string $efsExportDir;

    /**
     * @var string
     */
    public string $efsExportDirFolder;

    /**
     * @var string
     */
    public string $bucketPath;

    /**
     * @var string
     */
    public string $exportCsvFilePath;

    /**
     * Set folder property.
     *
     * @param int $queueId
     * @param int $actorId
     * @param string $expeditionUuid
     */
    public function setFolder(int $queueId, int $actorId, string $expeditionUuid): void
    {
        $this->expeditionUuid = $expeditionUuid;
        $this->folderName = $queueId.'-'.$actorId.'-'.$expeditionUuid;
    }

    /**
     * Set directories.
     *
     * @return void
     */
    public function setDirectories(): void
    {
        $this->setScratchDirectory();
        $this->setWorkingDirectory();
        $this->setExportDirectory();
        $this->setExportArchiveFileAndPath();
        $this->setExportCsvFilePath();
        $this->setEfsExportDirectory();
        $this->setBucketPath();
    }

    /**
     * Set scratch directory.
     */
    private function setScratchDirectory(): void
    {
        $this->scratchDir = config('config.scratch_dir');
    }


    /**
     * Set working directory.
     */
    private function setWorkingDirectory(): void
    {
        $this->workingDir = $this->scratchDir.'/'.$this->folderName;

        Storage::disk('s3')->makeDirectory($this->workingDir);
    }

    /**
     * Set export directory.
     */
    private function setExportDirectory(): void
    {
        $this->exportDirectory = config('config.export_dir');
    }

    /**
     * Set export csv file path.
     */
    private function setExportCsvFilePath(): void
    {
        $this->exportCsvFilePath = $this->workingDir.'/'.$this->expeditionUuid.'.csv';
    }

    /**
     * Set archive tar gz file and path.
     */
    private function setExportArchiveFileAndPath(): void
    {
        $this->exportArchiveFile = $this->folderName.'.zip';
        $this->exportArchiveFilePath = $this->exportDirectory.'/'.$this->exportArchiveFile;
    }

    /**
     * Set efs batch directory.
     *
     * @return void
     */
    private function setEfsExportDirectory(): void
    {
        $this->efsExportDir = config('config.export_dir');
        $this->efsExportDirFolder = $this->efsExportDir . '/' . $this->folderName;
        Storage::disk('efs')->makeDirectory($this->efsExportDirFolder);
    }

    /**
     * Set bucket path.
     *
     * @return void
     */
    private function setBucketPath(): void
    {
        $this->bucketPath = 's3://' . config('filesystems.disks.s3.bucket');
    }

    /**
     * Delete existing file.
     *
     * @param string $filePath
     */
    public function deleteEfsFile(string $filePath): void
    {
        if (Storage::disk('efs')->exists($filePath)) {
            Storage::disk('efs')->delete($filePath);
        }
    }

    /**
     * Delete existing file.
     *
     * @param string $filePath
     */
    public function deleteS3File(string $filePath): void
    {
        if (Storage::disk('s3')->exists($filePath)) {
            Storage::disk('s3')->delete($filePath);
        }
    }

    /**
     * Delete directory.
     *
     * @param string $dir
     * @return void
     */
    public function deleteEfsDirectory(string $dir): void
    {
        if (Storage::disk('efs')->exists($dir)) {
            Storage::disk('efs')->deleteDirectory($dir);
        }
    }

    /**
     * Delete directory.
     *
     * @param string $dir
     * @return void
     */
    public function deleteS3Directory(string $dir): void
    {
        if (Storage::disk('s3')->exists($dir)) {
            Storage::disk('s3')->deleteDirectory($dir);
        }
    }

    /**
     * Check if file exists.
     *
     * @param string $filePath
     * @return bool
     */
    public function checkS3FileExists(string $filePath): bool
    {
        return Storage::disk('s3')->exists($filePath);
    }
}