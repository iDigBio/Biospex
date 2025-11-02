<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Services\Actor\Zooniverse;

use App\Jobs\ZooniverseExportProcessImagesJob;
use App\Livewire\ProcessMonitor;
use App\Models\Download;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Services\Actor\ActorFactory;
use App\Services\Expedition\ExpeditionService;
use Illuminate\Support\Facades\Storage;

/**
 * Handles Zooniverse export queue operations and expedition data management.
 */
class ZooniverseExportQueue
{
    /**
     * ExportQueueCommand constructor.
     *
     * @param  ExpeditionService  $expeditionService  Service for expedition operations
     * @param  ExportQueue  $exportQueue  Export queue model
     * @param  Download  $download  Download model
     */
    public function __construct(
        protected ExpeditionService $expeditionService,
        protected ExportQueue $exportQueue,
        protected Download $download
    ) {}

    /**
     * Process export queue.
     * Gets first non-errored and non-queued export and dispatches it for processing.
     */
    public function processQueue(): void
    {
        \Log::info('Processing export queue...');
        $exportQueue = $this->exportQueue
            ->with('expedition')
            ->where('error', 0)
            ->where('queued', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $exportQueue) {
            \Log::info('No export queue items available.');

            return;
        }

        $exportQueue->queued = 1;
        $exportQueue->stage = 1;
        $exportQueue->save();

        ZooniverseExportProcessImagesJob::dispatch($exportQueue);
    }

    /**
     * Get export queue for stage command.
     *
     * @param  int  $queueId  The queue ID to retrieve
     * @return ExportQueue The export queue with its expedition relationship
     */
    public function getExportQueueForStageCommand(int $queueId): ExportQueue
    {
        return $this->exportQueue->with(['expedition'])->find($queueId);
    }

    /**
     * Handles resetting Expedition attributes from command line.
     *
     * @param  int  $expeditionId  The expedition ID to reset
     */
    public function resetExpeditionExport(int $expeditionId): void
    {
        \Log::info('Resetting expedition export for expedition ID: '.$expeditionId);
        $expedition = $this->expeditionService->getExpeditionForQueueReset($expeditionId);

        if (! is_null($expedition->exportQueue)) {
            $expedition->exportQueue->delete();
        }

        $this->resetExpeditionData($expedition);
    }

    /**
     * Reset data for expedition when regenerating export.
     * Deletes export files and updates actor expedition state.
     *
     * @param  Expedition  $expedition  The expedition to reset
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
     * Removes files from S3 storage and deletes corresponding download records.
     *
     * @param  string  $expeditionId  The expedition ID whose files should be deleted
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
