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

namespace App\Services\Actor\NfnPanoptes;

use App\Models\Actor;
use App\Services\Actor\ActorInterface;

/**
 * Class ZooniverseExportDeleteFiles
 *
 * @package App\Services\Actor
 */
class ZooniverseExportDeleteFiles extends ZooniverseBase implements ActorInterface
{
    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->dbService->exportQueueRepo->findByExpeditionAndActorId($actor->pivot->expedition_id, $actor->id);
        $queue->processed = 0;
        $queue->stage = 5;
        $queue->save();

        \Artisan::call('export:poll');

        try {
            $this->actorDirectory->setFolder($queue->id, $actor->id, $queue->expedition->uuid);
            $this->actorDirectory->setDirectories();
            \File::deleteDirectory($this->actorDirectory->workingDirectory);
            $queue->delete();

            \Artisan::call('export:poll');

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}