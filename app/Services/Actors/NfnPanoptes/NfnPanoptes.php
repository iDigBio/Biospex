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

use App\Jobs\ZooniverseCsvJob;
use App\Jobs\ZooniverseExportBuildQueueJob;
use App\Models\Actor;

/**
 * Class NfnPanoptes
 *
 * @package App\Services\Actor
 */
class NfnPanoptes
{
    /**
     * Process export job.
     *
     * State = 0: Expedition created.
     * State = 1: Export for Expedition. Set to 1 when export called and performed. @see \App\Console\Commands\ExportQueueCommand
     * State = 2: Will not run until process started and set to 2, added to WorkflowManager. @see \App\Http\Controllers\Admin\ZooniverseController
     * State = 3: Nfn classifications completed. @see \App\Console\Commands\ZooniverseClassificationCount
     *
     * @param \App\Models\Actor $actor
     * @throws \Throwable
     */
    public function actor(Actor $actor): void
    {
        if ($actor->pivot->state === 1) {
            ZooniverseExportBuildQueueJob::dispatch($actor);
        } elseif ($actor->pivot->state === 2) {
            ZooniverseCsvJob::dispatch($actor->pivot->expedition_id);
        }
    }
}