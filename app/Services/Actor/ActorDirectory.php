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

use Illuminate\Support\Facades\File;
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
    public string $scratchDirectory;

    /**
     * @var string
     */
    public string $workingDirectory;

    /**
     * @var string
     */
    public string $tmpDirectory;

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
     * Set folder property.
     *
     * @param int $queueId
     * @param int $actorId
     * @param string $expeditionUuid
     */
    public function setFolder(int $queueId, int $actorId, string $expeditionUuid)
    {
        $this->folderName = $queueId . '-' . $actorId . '-' . $expeditionUuid;
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
        $this->randomStr = md5(\Str::random(10) . $this->folderName);
    }

    /**
     * Set scratch directory.
     */
    private function setScratchDirectory()
    {
        $this->scratchDirectory = Storage::path(config('config.scratch_dir'));
    }

    /**
     * Set working directory.
     * If delete, clear existing folder of everything.
     *
     * @param bool $delete
     */
    private function setWorkingDirectory(bool $delete)
    {
        $this->workingDirectory = $this->scratchDirectory . '/' . $this->folderName;

        if (File::isDirectory($this->workingDirectory) && $delete) {
            File::deleteDirectory($this->workingDirectory);
        }

        File::makeDirectory($this->workingDirectory, 0777, true, true);
    }

    /**
     * Set tmp directory.
     */
    private function setTmpDirectory()
    {
        $this->tmpDirectory = $this->workingDirectory . '/tmp';
        File::makeDirectory($this->tmpDirectory, 0777, true, true);
    }

    /**
     * Set nfn export directory.
     */
    private function setExportDirectory()
    {
        $this->exportDirectory = Storage::path(config('config.export_dir'));
    }

    /**
     * Set gz archive and path.
     */
    private function setArchiveTar()
    {
        $this->archiveTar = $this->randomStr . '.tar';
        $this->archiveTarPath = $this->exportDirectory . '/' . $this->archiveTar;
    }

    /**
     * Set archive tar gz file and path.
     *
     * @param bool $batch
     */
    protected function setArchiveTarGz(bool $batch = false)
    {
        $this->archiveTarGz = $batch ? $this->folderName . '.tar.gz' : $this->randomStr . '.tar.gz';
        $this->archiveTarGzPath = $this->exportDirectory . '/' . $this->archiveTarGz;
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
        return $this->exportDirectory . '/' . $fileName . '.tar.gz';
    }

    /**
     * Delete existing file.
     *
     * @param string $filePath
     */
    public function deleteFile(string $filePath)
    {
        if (\File::exists($filePath)) {
            \File::delete($filePath);
        }
    }

}