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

use App\Models\Actor;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\File;
use Storage;

/**
 * Class ZooniverseBase
 *
 * @package App\Services\Actor
 */
class ZooniverseBase
{
    /**
     * @var ExportQueue
     */
    protected $queue;

    /**
     * @var \App\Models\Expedition
     */
    protected $expedition;

    /**
     * @var \App\Models\Actor
     */
    protected $actor;

    /**
     * @var \App\Models\User
     */
    protected $owner;

    /**
     * @var string
     */
    protected $folderName;

    /**
     * @var string
     */
    protected $scratchDirectory;

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var string
     */
    protected $tmpDirectory;

    /**
     * @var string
     */
    protected $nfnExportDirectory;

    /**
     * @var string
     */
    protected $archiveTar;

    /**
     * @var string
     */
    protected $archiveTarPath;

    /**
     * @var string
     */
    protected $archiveTarGz;

    /**
     * @var string
     */
    protected $archiveTarGzPath;

    /**
     * @var string
     */
    protected $randomStr;

    /**
     * Set queue property.
     *
     * @param \App\Models\ExportQueue $queue
     */
    public function setQueue(ExportQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Set expedition property.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function setExpedition(Expedition $expedition)
    {
        $this->expedition = $expedition;
    }

    /**
     * Set actor property.
     *
     * @param \App\Models\Actor $actor
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Set owner property.
     *
     * @param \App\Models\User $user
     */
    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

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
     * Creates random string for tar archive name.
     *
     * @return void
     */
    public function setRandomString()
    {
        $this->randomStr = md5(\Str::random(10) . $this->folderName);
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
     * Set directories.
     *
     * @param bool $delete
     * @param bool $batch
     * @return void
     * @throws \Exception
     */
    public function setDirectories(bool $delete = false, bool $batch = false)
    {
        if ($this->folderName === null) {
            throw new Exception(t('Folder required for export process is missing.'));
        }

        $this->setRandomString();
        $this->setScratchDirectory();
        $this->setWorkingDirectory($delete);
        $this->setTmpDirectory();
        $this->setNfnExportDirectory();
        $this->setArchiveTar();
        $this->setArchiveTarGz($batch);
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
    private function setNfnExportDirectory()
    {
        $this->nfnExportDirectory = Storage::path(config('config.export_dir'));
    }

    /**
     * Set gz archive and path.
     */
    private function setArchiveTar()
    {
        $this->archiveTar = $this->randomStr . '.tar';
        $this->archiveTarPath = $this->nfnExportDirectory . '/' . $this->archiveTar;
    }

    /**
     * Set archive tar gz file and path.
     *
     * @param bool $batch
     */
    protected function setArchiveTarGz(bool $batch = false)
    {
        $this->archiveTarGz = $batch ? $this->folderName . '.tar.gz' : $this->randomStr . '.tar.gz';
        $this->archiveTarGzPath = $this->nfnExportDirectory . '/' . $this->archiveTarGz;
    }

    /**
     * Set archive for batch files.
     *
     * @param string $fileName
     * @return string
     */
    protected function setBatchArchiveTarGz(string $fileName): string
    {
        return $this->nfnExportDirectory . '/' . $fileName . '.tar.gz';
    }

    /**
     * Check if converted file exists and is under file size.
     *
     * @param $file
     * @param bool $subject used if passing a subject id as file
     * @return bool
     */
    protected function checkConvertedFile($file, $subject = false): bool
    {
        $fileName = ! $subject ? File::name($file) : $file;
        $tmpFile = $this->tmpDirectory.'/'.$fileName.'.jpg';
        if (File::isFile($tmpFile)) {
            return true;
        }

        return false;
    }

    /**
     * Delete existing file.
     *
     * @param string $filePath
     */
    protected function deleteFile(string $filePath)
    {
        if (\File::exists($filePath)) {
            \File::delete($filePath);
        }
    }
}