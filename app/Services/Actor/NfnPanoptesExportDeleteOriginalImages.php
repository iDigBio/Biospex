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
     */
    public function process(ExportQueue $queue)
    {
        $this->setProperties($queue);

        $files = collect(File::files($this->workingDirectory));

        $files->each(function ($file) {
            File::delete($file);
        });

        ActorEventHelper::fireActorQueuedEvent($this->actor);

        $this->advanceQueue($queue);

        return;
    }
}