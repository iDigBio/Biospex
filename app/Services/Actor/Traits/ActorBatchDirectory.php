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
use mysql_xdevapi\SqlStatementResult;
use const Grpc\STATUS_OUT_OF_RANGE;

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

    public string $efsBatchDir;
    public string $fullEfsBatchDir;
    public string $batchWorkingDir;
    public string $fullBatchWorkingDir;
    public string $exportDir;
    public string $fullExportBatchDir;
    public string $existingExportFile;
    public string $existingExportFilePath;
    public string $batchTmpDir;

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
     * Backwards compatible for tar.gz.
     *
     * @param string $fileName
     */
    public function setBatchFolderName(string $fileName)
    {
        $filePath = Storage::disk('s3')->path(config('config.export_dir') . '/' . $fileName);
        $this->folderName = \File::name($filePath);
    }

    /**
     * Set directories.
     *
     * @return void
     */
    public function setBatchDirectories()
    {
        $this->setEfsBatchDirectory();
        $this->setBatchWorkingDirectory();
        $this->setExportDirectory();
        $this->setExportBatchDirectory();
        $this->setExistingExportFileAndPath();
    }

    /**
     * Set EFS batch directory.
     *
     * @return void
     */
    private function setEfsBatchDirectory()
    {
        $this->efsBatchDir = config('config.aws_efs_batch_dir');
        $this->fullEfsBatchDir = Storage::disk('efs')->path($this->efsBatchDir);
    }

    /**
     * Set working directory for batch file creation.
     *
     * @return void
     */
    private function setBatchWorkingDirectory()
    {
        $this->batchWorkingDir = $this->efsBatchDir.'/'.$this->folderName;
        Storage::disk('efs')->makeDirectory($this->batchWorkingDir);
        $this->fullBatchWorkingDir = Storage::disk('efs')->path($this->batchWorkingDir);
    }

    /**
     * Set nfn export directory.
     */
    private function setExportDirectory()
    {
        $this->exportDir = config('config.export_dir');
    }

    /**
     * Set export batches directory.
     *
     * @return void
     */
    public function setExportBatchDirectory()
    {
        $this->fullExportBatchDir = config('filesystems.disks.s3.bucket') . '/' . config('config.batch_dir');
    }

    /**
     * Set archive tar gz file and path.
     */
    protected function setExistingExportFileAndPath()
    {
        $this->existingExportFile = $this->folderName.'.zip';
        $this->existingExportFilePath = config('filesystems.disks.s3.bucket') . '/' . $this->exportDir.'/'.$this->existingExportFile;
    }

    /**
     * Check s3 export file exists.
     *
     * @return bool
     */
    public function checkS3ExportFileExists(): bool
    {
        return Storage::disk('s3')->exists($this->exportDir . '/' . $this->existingExportFile);
    }

    /**
     * Check existing export file exists in efs.
     *
     * @return bool
     */
    public function checkEfsExportFileExists(): bool
    {
        return Storage::disk('efs')->exists($this->efsBatchDir . '/' . $this->existingExportFile);
    }

    /**
     * Set temp directory for zipping batch files.
     *
     * @param string $dir
     * @return void
     */
    private function setBatchTmpDirectory(string $dir)
    {
        $this->batchTmpDir = $this->batchWorkingDir . '/' . $dir;
        Storage::disk('efs')->makeDirectory($this->batchTmpDir);
    }
}