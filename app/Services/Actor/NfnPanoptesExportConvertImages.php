<?php
/**
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

use App\Facades\ActorEventHelper;
use App\Models\ExportQueue;
use Exception;
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
        $this->setActor($queue->expedition->actors->first());
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
            ActorEventHelper::fireActorErrorEvent($this->actor);
            throw new Exception(t('The subject files do not exist.'));
        }

        ActorEventHelper::fireActorQueuedEvent($this->actor);
        $this->advanceQueue($queue);

        return;
    }
}