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

namespace App\Services\Actor\Zooniverse;

use App\Jobs\ZooniverseExportProcessImagesJob;
use App\Models\Download;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Services\Actor\ActorDirectory;
use App\Services\Actor\ActorFactory;
use App\Services\Expedition\ExpeditionService;
use Illuminate\Support\Facades\Storage;

class ZooniverseExportQueue
{
    /**
     * ExportQueueCommand constructor.
     */
    public function __construct(
        protected ExpeditionService $expeditionService,
        protected ExportQueue $exportQueue,
        protected ActorDirectory $actorDirectory,
        protected Download $download
    ) {}

    /**
     * Process export queue.
     */
    public function processQueue(): void
    {
        $exportQueue = $this->exportQueue->with('expedition')->where('error', 0)->first();

        if ($exportQueue === null || $exportQueue->queued === 1) {
            return;
        }

        $exportQueue->queued = 1;
        $exportQueue->save();

        $this->actorDirectory->setFolder($exportQueue->expedition_id, config('zooniverse.actor_id'), $exportQueue->expedition->uuid);
        $this->actorDirectory->setDirectories();

        ZooniverseExportProcessImagesJob::dispatch($exportQueue, $this->actorDirectory); // set stage 1
    }

    /**
     * Get export queue for stage command.
     */
    public function getExportQueueForStageCommand(int $queueId): ExportQueue
    {
        return $this->exportQueue->with(['expedition'])->find($queueId);
    }

    /**
     * Handles resetting Expedition attributes from command line.
     */
    public function resetExpeditionExport(int $expeditionId): void
    {
        $expedition = $this->expeditionService->getExpeditionForQueueReset($expeditionId);

        if (! is_null($expedition->exportQueue)) {
            $expedition->exportQueue->delete();
        }

        $this->resetExpeditionData($expedition);
    }

    /**
     * Reset data for expedition when regenerating export.
     */
    public function resetExpeditionData(Expedition $expedition): void
    {
        $this->deleteExportFiles($expedition->id);

        // Set actor_expedition pivot state to 1 if currently 0.
        // Otherwise, it's a regeneration export and state stays the same
        $attributes = [
            'state' => $expedition->zooActorExpedition->state === 0 ? 1 : $expedition->zooActorExpedition->state,
            'total' => $expedition->stat->local_subject_count,
        ];

        $expedition->zooActorExpedition->update($attributes);

        // Set state to 1 to handle regenerating exports without effecting database value.
        $expedition->zooActorExpedition->state = 1;

        ActorFactory::create($expedition->zooActorExpedition->actor->class)->process($expedition->zooActorExpedition);
    }

    /**
     * Delete existing exports files for expedition.
     */
    public function deleteExportFiles(string $expeditionId): void
    {
        $downloads = $this->download->where('actor_id', config('zooniverse.actor_id'))
            ->where('expedition_id', $expeditionId)
            ->where('type', 'export')
            ->get();

        $downloads->each(function ($download) {
            if (Storage::disk('s3')->exists(config('config.export_dir').'/'.$download->file)) {
                Storage::disk('s3')->delete(config('config.export_dir').'/'.$download->file);
            }

            if (Storage::disk('s3')->exists(config('config.report_dir').'/'.$download->file)) {
                Storage::disk('s3')->delete(config('config.report_dir').'/'.$download->file);
            }

            $download->delete();
        });
    }
}
