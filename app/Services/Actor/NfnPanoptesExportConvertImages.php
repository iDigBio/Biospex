<?php

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Models\ExportQueue;
use Illuminate\Support\Facades\File;

/**
 * Class NfnPanoptesExportConvertImages
 *
 * @see \App\Services\Actor\NfnPanoptes::processQueue()
 * @package App\Services\Actor
 */
class NfnPanoptesExportConvertImages extends NfnPanoptesBase
{
    /**
     * @var \App\Services\Actor\ActorImageService
     */
    private $actorImageService;

    /**
     * NfnPanoptesExportConvertImages constructor.
     *
     * @param \App\Services\Actor\ActorImageService $actorImageService
     */
    public function __construct(
        ActorImageService $actorImageService
    ) {

        $this->actorImageService = $actorImageService;
    }

    /**
     * Convert image stage.
     *
     * @param \App\Models\ExportQueue $queue
     * @throws \Exception
     */
    public function process(ExportQueue $queue)
    {
        $this->setQueue($queue);
        $this->setExpedition($queue->expedition);
        $this->setActor($queue->expedition->actor);
        $this->setOwner($queue->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories();

        $files = collect(File::files($this->workingDirectory));

        $this->actorImageService->setActor($this->actor);
        $this->actorImageService->setDirectories($this->workingDirectory, $this->tmpDirectory);
        $this->actorImageService->setFiles($files);

        $files->reject(function ($file) {
            if ($this->checkConvertedFile($file)) {
                ActorEventHelper::fireActorProcessedEvent($this->actor);

                return true;
            }

            return false;
        })->each(function ($file) {
            $fileName = File::name($file);
            $this->actorImageService->processFileImage($file, $fileName);
            ActorEventHelper::fireActorProcessedEvent($this->actor);
        });

        if (empty(File::files($this->tmpDirectory))) {
            ActorEventHelper::fireActorReportStageEvent($this->actor);

            return;
        }

        ActorEventHelper::fireActorQueuedEvent($this->actor);
        $this->advanceQueue($queue);

        return;
    }
}