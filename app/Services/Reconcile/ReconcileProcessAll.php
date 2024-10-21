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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Reconcile;

use App\Jobs\ZooniverseClassificationCountJob;
use App\Jobs\ZooniversePusherJob;
use App\Jobs\ZooniverseTranscriptionJob;
use App\Models\Download;
use App\Models\Expedition;
use Carbon\Carbon;

class ReconcileProcessAll
{
    public function __construct(
        protected Download $download,
        protected Carbon $carbon
    ) {}

    /**
     * Process returned reconcile event. Pass on to:
     *
     * @see ZooniverseTranscriptionJob
     * @see ZooniversePusherJob
     * @see ZooniverseClassificationCountJob
     */
    public function process(Expedition $expedition): void
    {
        $this->updateOrCreateDownloads($expedition->id);

        ZooniverseTranscriptionJob::withChain([
            new ZooniversePusherJob($expedition),
            new ZooniverseClassificationCountJob($expedition),
        ])->dispatch($expedition->id);
    }

    /**
     * Update or create downloads for reconcile files produced.
     */
    protected function updateOrCreateDownloads(int $expeditionId): void
    {
        collect(config('zooniverse.file_types'))->each(function ($type) use ($expeditionId) {
            $values = [
                'expedition_id' => $expeditionId,
                'actor_id' => config('zooniverse.actor_id'),
                'file' => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type' => $type,
                'updated_at' => $this->carbon->now()->format('Y-m-d H:i:s'),
            ];
            $attributes = [
                'expedition_id' => $expeditionId,
                'actor_id' => config('zooniverse.actor_id'),
                'file' => $type !== 'summary' ? $expeditionId.'.csv' : $expeditionId.'.html',
                'type' => $type,
            ];

            $this->download->updateOrCreate($attributes, $values);
        });
    }
}
