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

namespace App\Services\Actor;

use Illuminate\Support\Facades\Storage;

/**
 * ActorDirectory
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
    public string $randomStr;

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
    public string $tmpDir;

    /**
     * @var string
     */
    public string $exportDirectory;

    /**
     * @var string
     */
    public string $archiveTar;

    /**
     * @var string
     */
    public string $archiveTarPath;

    /**
     * @var string
     */
    public string $archiveTarGz;

    /**
     * @var string
     */
    public string $archiveTarGzPath;

    /**
     * @var array
     */
    public array $rejected  = [];

    /**
     * Set folder property.
     *
     * @param int $queueId
     * @param int $actorId
     * @param string $expeditionUuid
     */
    public function setFolder(int $queueId, int $actorId, string $expeditionUuid)
    {
        $this->folderName = $queueId.'-'.$actorId.'-'.$expeditionUuid;
    }

    /**
     * Set directories.
     *
     * @param bool $delete
     * @param bool $batch
     * @return void
     */
    public function setDirectories(bool $delete = false, bool $batch = false)
    {
        $this->setRandomString();
        $this->setScratchDirectory();
        $this->setWorkingDirectory($delete);
        $this->setTmpDirectory();
        $this->setExportDirectory();
        $this->setArchiveTar();
        $this->setArchiveTarGz($batch);
    }

    /**
     * Creates random string for tar archive name.
     *
     * @return void
     */
    public function setRandomString()
    {
        $this->randomStr = md5(\Str::random(10).$this->folderName);
    }

    /**
     * Set scratch directory.
     */
    private function setScratchDirectory()
    {
        $this->scratchDir = config('config.aws_s3_scratch_dir');
    }

    /**
     * Set working directory.
     * If delete, clear existing folder of everything.
     *
     * @param bool $delete
     */
    private function setWorkingDirectory(bool $delete)
    {
        $this->workingDir = $this->scratchDir.'/'.$this->folderName;

        if (Storage::disk('s3')->exists($this->workingDir) && $delete) {
            Storage::disk('s3')->deleteDirectory($this->workingDir);
        }

        Storage::disk('s3')->makeDirectory($this->workingDir);
    }

    /**
     * Set tmp directory.
     */
    private function setTmpDirectory()
    {
        $this->tmpDir = $this->workingDir.'/tmp';
        Storage::disk('s3')->makeDirectory($this->tmpDir);
    }

    /**
     * Set nfn export directory.
     */
    private function setExportDirectory()
    {
        $this->exportDirectory = config('config.aws_s3_export_dir');
    }

    /**
     * Set gz archive and path.
     */
    private function setArchiveTar()
    {
        $this->archiveTar = $this->randomStr.'.tar';
        $this->archiveTarPath = $this->exportDirectory.'/'.$this->archiveTar;
    }

    /**
     * Set archive tar gz file and path.
     *
     * @param bool $batch
     */
    protected function setArchiveTarGz(bool $batch = false)
    {
        $this->archiveTarGz = $batch ? $this->folderName.'.tar.gz' : $this->randomStr.'.tar.gz';
        $this->archiveTarGzPath = $this->exportDirectory.'/'.$this->archiveTarGz;
    }

    /**
     * Set folder name using already created download file name.
     *
     * @param string $fileName
     */
    public function setBatchFolder(string $fileName)
    {
        $folder = str_replace('.tar.gz', '', $fileName);
        $this->folderName = $folder;
    }

    /**
     * Set archive for batch files.
     *
     * @param string $fileName
     * @return string
     */
    public function setBatchArchiveTarGz(string $fileName): string
    {
        return $this->exportDirectory.'/'.$fileName.'.tar.gz';
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
        if (! Storage::disk('s3')->exists($filePath)) {
            if ($reject) {
                $this->rejected[$subjectId] = 'Image was not downloaded and converted.';
            }

            return false;
        }

        if (false === exif_imagetype($filePath)) {
            if ($reject) {
                $this->rejected[$subjectId] = 'Converted image file was corrupt.';
            }

            return false;
        }

        return true;
    }

    /**
     * Save file.
     *
     * @param string $filePath
     * @param string $content
     * @return void
     */
    public function putFile(string $filePath, string $content)
    {
        Storage::disk('s3')->put($filePath, $content);
    }
}