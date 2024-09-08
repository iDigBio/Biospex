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
use App\Services\Models\ExpeditionModelService;
use Illuminate\Support\Facades\Storage;

readonly class ZooniverseExportQueue
{
    /**
     * ExportQueueCommand constructor.
     */
    public function __construct(
        private ExpeditionModelService $expeditionModelService,
        private ExportQueue $exportQueue,
        private ActorDirectory $actorDirectory,
        private Download $download
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
        return $this->exportQueueRepository->findWith($queueId, ['expedition']);
    }

    /**
     * Handles resetting Expedition attributes from command line.
     */
    public function resetExpeditionExport(int $expeditionId): void
    {
        $expedition = $this->getExpedition($expeditionId);

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
            'state' => $expedition->zooniverseActor->pivot->state === 0 ? 1 : $expedition->zooniverseActor->pivot->state,
            'total' => $expedition->stat->local_subject_count,
        ];

        $expedition->zooniverseActor->expeditions()->updateExistingPivot($expedition->id, $attributes);

        // Set state to 1 to handle regenerating exports without effecting database value.
        $expedition->zooniverseActor->pivot->state = 1;

        ActorFactory::create($expedition->zooniverseActor->class)->actor($expedition->zooniverseActor);
    }

    /**
     * Delete existing exports files for expedition.
     */
    public function deleteExportFiles(string $expeditionId): void
    {
        $downloads = $this->download->getZooniverseExportFiles($expeditionId);

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

    /**
     * Get expedition with zooniverseActor and stat.
     */
    private function getExpedition(int $expeditionId): Expedition
    {
        return $this->expeditionModelService->findExpeditionWithRelations($expeditionId, ['zooniverseActor', 'stat', 'exportQueue']);
    }
}
