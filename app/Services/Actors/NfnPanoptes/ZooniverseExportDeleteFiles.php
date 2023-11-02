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

namespace App\Services\Actors\NfnPanoptes;

use App\Models\ExportQueue;
use App\Services\Actors\QueueInterface;
use App\Services\Actors\Traits\ActorDirectory;

/**
 * Class ZooniverseExportDeleteFiles
 */
class ZooniverseExportDeleteFiles implements QueueInterface
{
    use ActorDirectory;

    /**
     * Process actor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     */
    public function process(ExportQueue $exportQueue)
    {
        $exportQueue->load(['expedition']);

        $this->setFolder($exportQueue->id, $exportQueue->actor_id, $exportQueue->expedition->uuid);
        $this->setDirectories();
        $this->deleteDirectory($this->workingDir);
        $this->cleanLocalDirectory(\Storage::disk('efs')->path($this->efsExportDirFolder));
        $exportQueue->delete();

        \Artisan::call('export:poll');
        event('exportQueue.check');
    }
}