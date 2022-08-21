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
     * @param \App\Models\Actor $actor
     * @throws \Throwable
     */
    public function actor(Actor $actor)
    {
        if ($actor->pivot->state === 0) {
            ZooniverseExportBuildQueueJob::dispatch($actor);
        } elseif ($actor->pivot->state === 1) {
            ZooniverseCsvJob::dispatch($actor->pivot->expedition_id);
        }
    }
}