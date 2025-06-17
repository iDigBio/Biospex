<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
    public string $folderName;

    public string $scratchDir;

    public string $workingDir;

    public string $exportDirectory;

    public string $exportArchiveFile;

    public string $exportArchiveFilePath;

    public array $rejected = [];

    public string $expeditionUuid;

    public string $efsExportDir;

    public string $efsExportDirFolder;

    public string $bucketPath;

    public string $exportCsvFilePath;

    /**
     * Set folder property.
     */
    public function setFolder(int $queueId, int $actorId, string $expeditionUuid): void
    {
        $this->expeditionUuid = $expeditionUuid;
        $this->folderName = $queueId.'-'.$actorId.'-'.$expeditionUuid;
    }

    /**
     * Set directories.
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
     */
    private function setEfsExportDirectory(): void
    {
        $this->efsExportDir = config('config.export_dir');
        $this->efsExportDirFolder = $this->efsExportDir.'/'.$this->folderName;
        Storage::disk('efs')->makeDirectory($this->efsExportDirFolder);
    }

    /**
     * Set bucket path.
     */
    private function setBucketPath(): void
    {
        $this->bucketPath = 's3://'.config('filesystems.disks.s3.bucket');
    }

    /**
     * Delete existing file.
     */
    public function deleteEfsFile(string $filePath): void
    {
        if (Storage::disk('efs')->exists($filePath)) {
            Storage::disk('efs')->delete($filePath);
        }
    }

    /**
     * Delete existing file.
     */
    public function deleteS3File(string $filePath): void
    {
        if (Storage::disk('s3')->exists($filePath)) {
            Storage::disk('s3')->delete($filePath);
        }
    }

    /**
     * Delete directory.
     */
    public function deleteEfsDirectory(string $dir): void
    {
        if (Storage::disk('efs')->exists($dir)) {
            Storage::disk('efs')->deleteDirectory($dir);
        }
    }

    /**
     * Delete directory.
     */
    public function deleteS3Directory(string $dir): void
    {
        if (Storage::disk('s3')->exists($dir)) {
            Storage::disk('s3')->deleteDirectory($dir);
        }
    }

    /**
     * Check if file exists.
     */
    public function checkS3FileExists(string $filePath): bool
    {
        return Storage::disk('s3')->exists($filePath);
    }
}
