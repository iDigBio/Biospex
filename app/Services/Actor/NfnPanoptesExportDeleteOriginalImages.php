<?php

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Models\ExportQueue;
use File;

/**
 * Class NfnPanoptesExportDeleteOriginalImages
 *
 * @see \App\Services\Actor\NfnPanoptes::processQueue()
 * @package App\Services\Actor
 */
class NfnPanoptesExportDeleteOriginalImages extends NfnPanoptesBase
{
    /**
     * Delete original files to save space on server.
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

        $files->each(function ($file) {
            File::delete($file);
        });

        ActorEventHelper::fireActorQueuedEvent($this->actor);

        $this->advanceQueue($queue);

        return;
    }
}