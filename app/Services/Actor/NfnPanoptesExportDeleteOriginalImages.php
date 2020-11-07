<?php
/*
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
        $this->setActor($queue->expedition->actors->first());
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