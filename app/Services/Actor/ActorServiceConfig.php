<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PharData;

class ActorServiceConfig
{
    /**
     * @var
     */
    public $config;

    /**
     * @var ExportQueue
     */
    public $queue;

    /**
     * @var Expedition
     */
    public $expedition;

    /**
     * @var User
     */
    public $owner;

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
        $this->setOwner($queue->expedition->project->group->owner);
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
     * @param ExportQueue $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param Expedition $expedition
     */
    public function setExpedition($expedition)
    {
        $this->expedition = $expedition;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @param Actor $actor
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
        $this->scratchDirectory = Storage::path(config('config.scratch_dir'));
    }

    /**
     * Set working directory.
     */
    public function setWorkingDirectory()
    {
        $this->workingDirectory = $this->scratchDirectory . '/' . $this->folderName;
        Storage::makeDirectory($this->workingDirectory);
    }

    /**
     * Set tmp directory.
     */
    public function setTmpDirectory()
    {
        $this->tmpDirectory = $this->workingDirectory . '/tmp';
        Storage::makeDirectory($this->tmpDirectory);
    }

    /**
     * Set nfn export directory.
     */
    public function setNfnExportDirectory()
    {
        $this->nfnExportDirectory = Storage::path(config('config.export_dir'));
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
     * Check file exists.
     *
     * @param $file
     * @return bool
     */
    public function isFile($file)
    {
        return Storage::exists($file);
    }

    /**
     * Delete tmp directory
     */
    public function deleteScratchTmpDir()
    {
        if (! Storage::exists(config('config.scratch_dir_tmp')))
        {
            Storage::deleteDirectory(config('config.scratch_dir_tmp'));
        }
    }

    /**
     * Fire actor update for processed count.
     */
    public function fireActorProcessedEvent()
    {
        $this->actor->pivot->processed++;
        event('actor.pivot.processed', $this->actor);
    }

    /**
     * Fire actor queued event.
     */
    public function fireActorQueuedEvent()
    {
        $this->actor->pivot->processed = 0;
        $this->actor->pivot->queued = 1;

        event('actor.pivot.queued', $this->actor);
    }

    /**
     * Fire actor unqueued event.
     */
    public function fireActorUnQueuedEvent()
    {
        $this->actor->pivot->processed = 0;
        $this->actor->pivot->queued = 0;

        event('actor.pivot.unqueued', $this->actor);
    }

    /**
     * Fire actor state event.
     */
    public function fireActorStateEvent()
    {
        $this->actor->pivot->state++;
        $this->actor->pivot->processed = 0;
        $this->actor->pivot->queued = 0;

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
        $collection->queued = 0;
        $collection->error = 1;

        event('actor.pivot.error', $collection);
    }

    /**
     * Fire actor completed event.
     */
    public function fireActorCompletedEvent()
    {
        $this->actor->pivot->state++;
        $this->actor->pivot->queued = 0;
        $this->actor->pivot->completed = 1;

        event('actor.pivot.completed', $this->actor);
    }

}