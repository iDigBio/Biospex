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

namespace App\Services\Actor\Traits;

use App\Models\Actor;
use App\Models\Download;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

trait ActorBatchDirectory
{
    /**
     * @var \App\Models\Expedition
     */
    public Expedition $expedition;

    /**
     * @var \App\Models\Actor
     */
    public Actor $actor;

    /**
     * @var \App\Models\User
     */
    public User $owner;

    /**
     * @var string
     */
    public string $folderName;

    public string $fileExtension;

    public string $efsBatchDir;

    public string $fullEfsBatchDir;

    public string $batchWorkingDir;

    public string $fullBatchWorkingDir;

    public string $exportDir;

    public string $bucketBatchDir;

    public string $existingExportFile;

    public string $existingBucketExportFile;

    public string $batchTmpDir;

    public string $bucketPath;

    public string $fullBatchTmpDir;

    /**
     * Set properties.
     *
     * @param \App\Models\Download $download
     * @throws \Exception
     */
    private function setProperties(Download $download)
    {
        $this->expedition = $download->expedition;
        $this->actor = $download->actor;
        $this->owner = $download->expedition->project->group->owner;
    }

    /**
     * Set folder name using already created download file name.
     * Backwards compatible for tar.gz. TODO can eventually remove when tar.gz not used.
     *
     * @param string $fileName
     */
    public function setBatchFolderName(string $fileName)
    {
        $this->folderName = $this->stripExtension($fileName);
    }

    /**
     * Strip file extension and return name.
     * Backwards compatible with .tar.gz files.
     *
     * @param string $fileName
     * @return array|string
     */
    public function stripExtension(string $fileName): array|string
    {
        if (str_ends_with($fileName, '.tar.gz')) {
            $this->fileExtension = '.tar.gz';
            return str_replace('.tar.gz', '', $fileName);
        }

        $this->fileExtension = '.zip';
        return \File::name($fileName);
    }

    /**
     * Set directories.
     *
     * @return void
     */
    public function setBatchDirectories()
    {
        $this->setBucketPath();
        $this->setEfsBatchDirectory();
        $this->setBatchWorkingDirectory();
        $this->setExportDirectory();
        $this->setExportBatchDirectory();
        $this->setExistingExportFileAndPath();
    }

    /**
     * Set bucket path for aws s3 commands.
     *
     * @return void
     */
    private function setBucketPath()
    {
        $this->bucketPath = 's3://'.config('filesystems.disks.s3.bucket');
    }

    /**
     * Set EFS batch directory.
     *
     * @return void
     */
    private function setEfsBatchDirectory(): void
    {
        $this->efsBatchDir = config('config.batch_dir');
        $this->fullEfsBatchDir = Storage::disk('efs')->path($this->efsBatchDir);
    }

    /**
     * Set working directory for batch file creation.
     *
     * @return void
     */
    private function setBatchWorkingDirectory(): void
    {
        $this->batchWorkingDir = $this->efsBatchDir.'/'.$this->folderName;
        Storage::disk('efs')->makeDirectory($this->batchWorkingDir);
        $this->fullBatchWorkingDir = Storage::disk('efs')->path($this->batchWorkingDir);
    }

    /**
     * Set zooniverse export directory.
     */
    private function setExportDirectory(): void
    {
        $this->exportDir = config('config.export_dir');
    }

    /**
     * Set export batches directory.
     *
     * @return void
     */
    public function setExportBatchDirectory(): void
    {
        $this->bucketBatchDir = $this->bucketPath.'/'.config('config.batch_dir');
    }

    /**
     * Set archive tar gz file and path.
     */
    protected function setExistingExportFileAndPath(): void
    {
        $this->existingExportFile = $this->folderName.$this->fileExtension;
        $this->existingBucketExportFile = $this->bucketPath.'/'.$this->exportDir.'/'.$this->existingExportFile;
    }

    /**
     * Set temp directory for zipping batch files.
     *
     * @param string $dir
     * @return void
     */
    private function setBatchTmpDirectory(string $dir): void
    {
        $this->batchTmpDir = $this->batchWorkingDir.'/'.$dir;
        Storage::disk('efs')->makeDirectory($this->batchTmpDir);
        $this->fullBatchTmpDir = Storage::disk('efs')->path($this->batchTmpDir);
    }

    /**
     * Check s3 export file exists.
     *
     * @return bool
     */
    public function checkS3ExportFileExists(): bool
    {
        return Storage::disk('s3')->exists($this->exportDir.'/'.$this->existingExportFile);
    }

    /**
     * Check existing export file exists in efs.
     *
     * @return bool
     */
    public function checkEfsExportFileExists(): bool
    {
        return Storage::disk('efs')->exists($this->efsBatchDir.'/'.$this->existingExportFile);
    }
}