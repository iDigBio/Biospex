<?php

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Repositories\Interfaces\ExportQueueFile;
use App\Models\ExportQueue;

/**
 * Class NfnPanoptesExportRetrieveImages
 *
 * @see \App\Services\Actor\NfnPanoptes::processQueue()
 * @package App\Services\Actor
 */
class NfnPanoptesExportRetrieveImages extends NfnPanoptesBase
{
    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private $actorImageService;

    /**
     * @var \App\Repositories\Interfaces\ExportQueueFile
     */
    private $exportQueueFileContract;

    /**
     * NfnPanoptesExportRetrieveImages constructor.
     *
     * @param \App\Services\Actor\ActorImageService $actorImageService
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFileContract
     */
    public function __construct(
        ActorImageService $actorImageService,
        ExportQueueFile $exportQueueFileContract
    )
    {
        $this->actorImageService = $actorImageService;
        $this->exportQueueFileContract = $exportQueueFileContract;
    }

    /**
     * Retrieve images stage.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \Exception
     */
    public function process(ExportQueue $queue)
    {
        $this->setProperties($queue);

        $files = $this->exportQueueFileContract->getFilesByQueueId($queue->id);
        if ($files->isEmpty())
        {
            throw new \Exception('Missing export subjects for Expedition ID ' . $queue->expedition_id);
        }

        $this->actorImageService->setActor($this->actor);
        $this->actorImageService->setDirectories($this->workingDirectory, $this->tmpDirectory);
        $this->actorImageService->setFiles($files);
        $this->actorImageService->getImages();

        ActorEventHelper::fireActorQueuedEvent($this->actor);

        $this->advanceQueue($queue);

        return;
    }
}