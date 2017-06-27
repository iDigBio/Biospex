<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\Expedition;
use App\Models\ExportQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PharData;

class ActorServiceConfig
{

    /**
     * @var ExportQueue
     */
    public $queue;

    /**
     * @var Expedition
     */
    public $expedition;

    /**
     * @var Actor
     */
    public $actor;

    /**
     * @var Collection
     */
    public $subjects;

    /**
     * @var string
     */
    public $folderName;

    /**
     * @var string
     */
    public $scratchDirectory;

    /**
     * @var string
     */
    public $workingDirectory;

    /**
     * @var string
     */
    public $tmpDirectory;

    /**
     * @var string
     */
    public $nfnExportDirectory;

    /**
     * @var string
     */
    public $archiveTar;

    /**
     * @var string
     */
    public $archiveTarPath;

    /**
     * @var string
     */
    public $archiveTarGz;

    /**
     * @var string
     */
    public $archiveTarGzPath;

    /**
     * @var PharData
     */
    public $archivePhar;

    /**
     * @var string
     */
    public $archiveExportPath;

    /**
     * @param $queue
     */
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

    /**
     * @param $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param $expedition
     */
    public function setExpedition($expedition)
    {
        $this->expedition = $expedition;
    }

    /**
     * @param $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * @param $subjects
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * Set working folder name.
     */
    public function setFolderName()
    {
        $this->folderName = $this->actor->id . '-' . $this->expedition->uuid;
    }

    /**
     * Set scratch directory.
     */
    public function setScratchDirectory()
    {
        $this->scratchDirectory = config('config.scratch_dir');
    }

    /**
     * Set working directory.
     */
    public function setWorkingDirectory()
    {
        $this->workingDirectory = $this->scratchDirectory . '/' . $this->folderName;
        File::makeDirectory($this->workingDirectory, 0775, true, true);
    }

    /**
     * Set tmp directory.
     */
    public function setTmpDirectory()
    {
        $this->tmpDirectory = $this->workingDirectory . '/tmp';
        File::makeDirectory($this->tmpDirectory, 0775, true, true);
    }

    /**
     * Set nfn export directory.
     */
    public function setNfnExportDirectory()
    {
        $this->nfnExportDirectory = config('config.nfn_export_dir');
    }

    /**
     * Set tar archive and path.
     */
    public function setArchiveTar()
    {
        $this->archiveTar = $this->folderName . '.tar';
        $this->archiveTarPath = $this->scratchDirectory . '/' . $this->archiveTar;
    }

    /**
     * Set gz archive and path.
     */
    public function setArchiveTarGz()
    {
        $this->archiveTarGz = $this->folderName . '.tar.gz';
        $this->archiveTarGzPath = $this->scratchDirectory . '/' . $this->archiveTarGz;
    }

    /**
     * Create new Phar archive.
     */
    public function setArchivePhar()
    {
        $this->archivePhar = new PharData($this->archiveTarPath);
    }

    /**
     * Set archive destination path.
     */
    public function setArchiveExportPath()
    {
        $this->archiveExportPath = $this->nfnExportDirectory . '/' . $this->archiveTarGz;
    }

    /**
     * Delete tmp directory
     */
    public function deleteScratchTmpDir()
    {
        if (! \File::exists(config('config.scratch_dir_tmp')))
        {
            \File::deleteDirectory(config('config.scratch_dir_tmp'));
        }
    }

    /**
     * Fire actor update for processed count.
     */
    public function fireActorProcessedEvent()
    {

        $this->actor->pivot->processed++;
        event('actor.pivot.processed', $this->actor);

        /* TODO figure out how to use subject count so update is not happening each time image is processed
        /* TODO When compressing files,
        $count = null !== $this->subjects ? $this->subjects->count() : $this->actor->pivot->total;
        if ($this->actor->pivot->processed % 25 === 0 || ($count - $this->actor->pivot->processed === 0) )
        {

        }
        */
    }

    /**
     * Fire actor queued event.
     */
    public function fireActorQueuedEvent()
    {
        event('actor.pivot.queued', $this->actor);
    }

    /**
     * Fire actor unqueued event.
     */
    public function fireActorUnQueuedEvent()
    {
        event('actor.pivot.unqueued', $this->actor);
    }

    /**
     * Fire actor state event.
     */
    public function fireActorStateEvent()
    {
        $this->actor->pivot->state++;
        event('actor.pivot.state', $this->actor);
    }

    /**
     * Fire actor error event.
     *
     * @param null $actor
     */
    public function fireActorErrorEvent($actor = null)
    {
        $collection = $actor === null ? $this->actor : $actor;
        event('actor.pivot.error', $collection);
    }

    /**
     * Fire actor completed event.
     */
    public function fireActorCompletedEvent()
    {
        event('actor.pivot.completed', $this->actor);
    }

}