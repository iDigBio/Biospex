<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\ActorException;
use App\Exceptions\BiospexException;
use App\Services\Actor\ActorImageService;
use App\Services\Actor\ActorRepositoryService;
use App\Services\File\FileService;
use App\Models\Actor;
use App\Models\StagedQueue;
use Exception;
use Illuminate\Contracts\Events\Dispatcher as Event;
use App\Services\Report\Report;
use App\Listeners\StagedQueueEventListener;
use PharData;


class NfnPanoptesExport
{

    /**
     * @var ActorRepositoryService
     */
    private $actorRepositoryService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ActorImageService
     */
    private $actorImageService;

    /**
     * @var Event
     */
    private $dispatcher;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var mixed
     */
    private $stages;

    /**
     * NfnPanoptesExport constructor.
     *
     * @param ActorRepositoryService $actorRepositoryService
     * @param ActorImageService $actorImageService
     * @param FileService $fileService
     * @param Event $dispatcher
     * @param Report $report
     */
    public function __construct(
        ActorRepositoryService $actorRepositoryService,
        ActorImageService $actorImageService,
        FileService $fileService,
        Event $dispatcher,
        Report $report
    )
    {
        $this->actorRepositoryService = $actorRepositoryService;
        $this->actorImageService = $actorImageService;
        $this->fileService = $fileService;
        $this->dispatcher = $dispatcher;
        $this->report = $report;

        $this->stages = [
            'retrieve',
            'convert',
            'compress',
            'report',
        ];

    }

    /**
     * Queue jobs for exports.
     *
     * @param Actor $actor
     * @see NfnPanoptes::actor() To set actor for this method.
     * @see StagedQueueEventListener::stagedQueueSaved() Event fired when queues saved.
     */
    public function stagedQueue(Actor $actor)
    {
        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id'      => $actor->id,
            'stage'         => 0
        ];
        $this->actorRepositoryService->createStagedQueue($attributes);
    }

    /**
     * Process export.
     *
     * @param StagedQueue $queue
     * @see NfnPanoptes::queue() Directs queud job to determine what stage to run.
     */
    public function queue(StagedQueue $queue)
    {
        try
        {
            $this->{[$queue->stage]}($queue);
        }
        catch (BiospexException $e)
        {
            $this->report->addError($e->getMessage());
            $this->report->reportError();
            $attributes = ['queued' => 0, 'error' => 1];
            $this->actorRepositoryService->updateStagedQueue($queue->id, $attributes);
        }
    }

    /**
     * @param StagedQueue $queue
     * @throws ActorException
     * @see NfnPanoptesExport::queue() Called from method if stage is exporting images.
     */
    public function retrieve(StagedQueue $queue)
    {
        $subjects = $this->actorRepositoryService->getSubjectsByExpeditionId($queue->expedition->id);
        if ($subjects->isEmpty())
        {
            throw new ActorException('Missing export subjects for Expedition ' . $queue->expedition->id);
        }

        $directory = $queue->expedition->actor->id . '-' . $queue->expedition->uuid;
        $workingDirectory = $this->actorImageService->setWorkingDirectory($directory);
        $this->fileService->makeDirectory($workingDirectory);

        $this->actorImageService->setActor($queue->expedition->actor);
        $this->actorImageService->getImages($subjects);

        $attributes = [
            'stage'   => 1,
            'missing' => $this->actorImageService->getMissingImages()
        ];
        $this->actorRepositoryService->updateStagedQueue($queue->id, $attributes);

        $queue->expedition->actor->pivot->processed = 0;
        $data = [$queue->expedition->actor, $queue->expedition->actor->pivot->total];
        $this->dispatcher->fire('actor.pivot.queued', $data);
    }

    /**
     * Convert image stage.
     *
     * @param StagedQueue $queue
     * @throws ActorException
     */
    public function convert(StagedQueue $queue)
    {
        try
        {

            $this->actorImageService->setActor($queue->expedition->actor);

            $dirName = $queue->expedition->actor->id . '-' . $queue->expedition->uuid;
            $scratchDirectory = $this->actorImageService->getScratchDirectory();
            $workingDirectory = $this->actorImageService->setWorkingDirectory($dirName);
            $tmpDir = $workingDirectory . '/tmp';
            $this->fileService->makeDirectory($tmpDir);

            $archive = new PharData($scratchDirectory . '/' . $dirName . '.tar');
            $files = collect($this->fileService->filesystem->files($workingDirectory));
            $files->each(function ($file) use ($tmpDir, $archive)
            {
                $fileName = $this->fileService->filesystem->name($file);
                $this->actorImageService->writeImagickFile($file, $tmpDir, $fileName);
                $archive->addFile($tmpDir . '/' . $fileName . '.jpg', $fileName . '.jpg');
            });

            $attributes = [
                'stage'   => 2,
                'missing' => array_merge($queue->missing, $this->actorImageService->getMissingImages())
            ];

            $queue->expedition->actor->pivot->processed = 0;
            $data = [$queue->expedition->actor, $queue->expedition->actor->pivot->total];
            $this->dispatcher->fire('actor.pivot.queued', $data);
            $this->actorRepositoryService->updateStagedQueue($queue->id, $attributes);

        }
        catch (Exception $e)
        {
            throw new ActorException($e);
        }
    }

    public function compress(StagedQueue $queue)
    {
        // tmp.tar  to .gz
        // compress the tar file
        // save file to database table

        $attributes = [
            'stage'   => 3,
            'missing' => array_merge($queue->missing, $this->actorImageService->getMissingImages())
        ];

        $data = [$queue->expedition->actor, $queue->expedition->actor->pivot->total];
        $this->dispatcher->fire('actor.pivot.queued', $data);

        $this->actorRepositoryService->updateStagedQueue($queue->id, $attributes);
    }

    public function report(StagedQueue $queue)
    {
        // send report
        // get missing images data

    }
}
