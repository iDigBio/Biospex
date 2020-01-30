<?php

namespace App\Services\Actor;

use App\Models\ExportQueue;
use Illuminate\Support\Facades\File;

class NfnPanoptesBase
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
    protected $archiveTarGz;

    /**
     * @var string
     */
    protected $archiveExportPath;

    /**
     * @param \App\Models\ExportQueue $queue
     */
    public function setProperties(ExportQueue $queue)
    {
        $this->queue = $queue;
        $this->expedition = $queue->expedition;
        $this->actor = $queue->expedition->actor;
        $this->owner = $queue->expedition->project->group->owner;

        $this->folderName = $queue->batch . '-' . $this->actor->id . '-' . $this->expedition->uuid;

        $this->setScratchDirectory();
        $this->setWorkingDirectory();
        $this->setTmpDirectory();
        $this->setNfnExportDirectory();
        $this->setArchiveTarGz();
    }

    /**
     * Set scratch directory.
     */
    public function setScratchDirectory()
    {
        $this->scratchDirectory = \Storage::path(config('config.scratch_dir'));
    }

    /**
     * Set working directory.
     */
    public function setWorkingDirectory()
    {
        $this->workingDirectory = $this->scratchDirectory . '/' . $this->folderName;
        File::makeDirectory($this->workingDirectory, 0777, true, true);
    }

    /**
     * Set tmp directory.
     */
    public function setTmpDirectory()
    {
        $this->tmpDirectory = $this->workingDirectory . '/tmp';
        File::makeDirectory($this->tmpDirectory, 0777, true, true);
    }

    /**
     * Set nfn export directory.
     */
    public function setNfnExportDirectory()
    {
        $this->nfnExportDirectory = \Storage::path(config('config.export_dir'));
    }

    /**
     * Set gz archive and path.
     */
    public function setArchiveTarGz()
    {
        $this->archiveTarGz = $this->folderName . '.tar.gz';
        $this->archiveExportPath = $this->nfnExportDirectory . '/' . $this->archiveTarGz;
    }

    /**
     * Check file exists.
     *
     * @param $file
     * @return bool
     */
    public function isFile($file)
    {
        return File::isFile($file);
    }

    /**
     * Advance the queue to the next stage.
     */
    protected function advanceQueue(ExportQueue $queue)
    {
        $queue->stage++;
        $queue->save();

        event('exportQueue.updated');

        return;
    }

    /**
     * Check if converted file exists and is under file size.
     *
     * @param $file
     * @param bool $subject used if passing a subject id as file
     * @return bool
     */
    protected function checkConvertedFile($file, $subject = false)
    {
        $fileName = ! $subject ? File::name($file) : $file;
        $tmpFile = $this->tmpDirectory.'/'.$fileName.'.jpg';
        if ($this->isFile($tmpFile)) {
            return true;
        }

        return false;
    }
}