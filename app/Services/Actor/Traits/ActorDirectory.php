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

use Illuminate\Support\Facades\Storage;

/**
 * Trait ActorDirectory
 */
trait ActorDirectory
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
    public string $expeditiionUuid;

    /**
     * @var string
     */
    public string $efsBatchDir;

    /**
     * @var string
     */
    public string $batchWorkingDir;

    /**
     * @var string
     */
    public string $batchTmpDir;

    /**
     * Set folder property.
     *
     * @param int $queueId
     * @param int $actorId
     * @param string $expeditionUuid
     */
    public function setFolder(int $queueId, int $actorId, string $expeditionUuid)
    {
        $this->expeditiionUuid = $expeditionUuid;
        $this->folderName = $queueId.'-'.$actorId.'-'.$expeditionUuid;
    }

    /**
     * Set directories.
     *
     * @return void
     */
    public function setDirectories()
    {
        $this->setScratchDirectory();
        $this->setWorkingDirectory();
        $this->setExportDirectory();
        $this->setExportArchiveFileAndPath();
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
        $this->setExportArchiveFileAndPath();
    }

    /**
     * Set scratch directory.
     */
    private function setScratchDirectory()
    {
        $this->scratchDir = config('config.scratch_dir');
    }

    /**
     * Set EFS batch directory.
     *
     * @return void
     */
    private function setEfsBatchDirectory()
    {
        $this->efsBatchDir = config('config.aws_efs_batch_dir');
    }

    /**
     * Set working directory.
     */
    private function setWorkingDirectory()
    {
        $this->workingDir = $this->scratchDir.'/'.$this->folderName;

        Storage::disk('s3')->makeDirectory($this->workingDir);
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

    /**
     * Set nfn export directory.
     */
    private function setExportDirectory()
    {
        $this->exportDirectory = config('config.export_dir');
    }

    /**
     * Set archive tar gz file and path.
     */
    protected function setExportArchiveFileAndPath()
    {
        $this->exportArchiveFile = $this->folderName.'.zip';
        $this->exportArchiveFilePath = $this->exportDirectory.'/'.$this->exportArchiveFile;
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
     * Set batch zip file names.
     *
     * @param string $fileName
     * @return string
     */
    public function setBatchExportZipFile(string $fileName): string
    {
        return $this->exportDirectory . '/' . $fileName . '.zip';
    }

    /**
     * Delete existing file.
     *
     * @param string $filePath
     */
    public function deleteFile(string $filePath)
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
    public function deleteDirectory(string $dir)
    {
        if (Storage::disk('s3')->exists($dir)) {
            Storage::disk('s3')->deleteDirectory($dir);
        }
    }

    /**
     * Check if file exists and is image.
     *
     * @param string $filePath
     * @param string $subjectId
     * @param bool $reject
     * @return bool
     */
    public function checkFileExists(string $filePath, string $subjectId, bool $reject = true): bool
    {
        if (Storage::disk('s3')->missing($filePath)) {
            if ($reject) {
                $this->rejected[$subjectId] = 'Image was not downloaded and converted.';
            }

            return false;
        }

        return true;
    }

    /**
     * Clean directory by deleting all files.
     *
     * @param string $dirPath
     * @return void
     */
    public function cleanDirectory(string $dirPath)
    {
        \File::cleanDirectory($dirPath);
    }
}