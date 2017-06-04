<?php

namespace App\Services\Actor;


use PharData;

class ActorServiceConfig
{
    public $queue;
    public $actor;
    public $expedition;
    public $subjects;
    public $folderName;
    public $scratchDirectory;
    public $workingDirectory;
    public $tmpDirectory;
    public $nfnExportDirectory;
    public $archiveTar;
    public $archiveTarPath;
    public $archiveTarGz;
    public $archiveTarGzPath;
    public $archivePhar;
    public $archiveExportPath;

    public function setProperties($queue)
    {
        $this->setQueue($queue);
        $this->setActor($queue->expedition->actor);
        $this->setExpedition($queue->expedition);
        $this->setFolderName();
        $this->setScratchDirectory();
        $this->setWorkingDirectory();
        $this->setTmpDirectory();
        $this->setNfnExportDirectory();
        $this->setArchiveTar();
        $this->setArchiveTarGz();
        $this->setArchivePhar();
        $this->setArchiveExportPath();
    }

    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
    }

    private function setQueue($queue)
    {
        $this->queue = $queue;
    }

    private function setActor($actor)
    {
        $this->actor = $actor;
    }

    private function setExpedition($expedition)
    {
        $this->expedition = $expedition;
    }

    private function setFolderName()
    {
        $this->folderName = $this->actor->id . '-' . $this->expedition->uuid;
    }

    private function setScratchDirectory()
    {
        $this->scratchDirectory = config('config.scratch_dir');
    }

    private function setWorkingDirectory()
    {
        $this->workingDirectory = $this->scratchDirectory . '/' . $this->folderName;
    }

    private function setTmpDirectory()
    {
        $this->tmpDirectory = $this->workingDirectory . '/tmp';
    }

    private function setNfnExportDirectory()
    {
        $this->nfnExportDirectory = config('config.nfn_export_dir');
    }

    private function setArchiveTar()
    {
        $this->archiveTar = $this->folderName . '.tar';
        $this->archiveTarPath = $this->workingDirectory . '/' . $this->archiveTar;
    }

    private function setArchiveTarGz()
    {
        $this->archiveTarGz = $this->folderName . '.tar.gz';
        $this->archiveTarGzPath = $this->workingDirectory . '/' . $this->archiveTarGz;
    }

    private function setArchivePhar()
    {
        $this->archivePhar = new PharData($this->archiveTarPath);
    }

    private function setArchiveExportPath()
    {
        $this->archiveExportPath = $this->nfnExportDirectory . '/' . $this->archiveTarGz;
    }

}