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

namespace App\Jobs;

use App\Models\ActorExpedition;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Actor\Zooniverse\ZooniverseExportQueueService;
use App\Services\Subject\SubjectService;
use App\Traits\NotifyOnJobFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Job to build export queue for Zooniverse expedition data.
 * Creates or updates export queue entries and builds associated files for subjects.
 */
class ZooniverseExportBuildQueueJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotifyOnJobFailure, Queueable;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param  ActorExpedition  $actorExpedition  The actor expedition instance
     */
    public function __construct(protected ActorExpedition $actorExpedition)
    {
        $this->actorExpedition = $actorExpedition->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     * Creates or updates the export queue and builds associated files for subjects.
     *
     * @param  SubjectService  $subjectService  Service to handle subject operations
     *
     * @throws \Exception
     */
    public function handle(
        SubjectService $subjectService,
        ZooniverseExportQueueService $queueService): void
    {

        $this->actorExpedition->load('expedition');

        // === CREATE OR UPDATE EXPORT QUEUE ===
        $queue = ExportQueue::firstOrCreate([
            'expedition_id' => $this->actorExpedition->expedition_id,
            'actor_id' => $this->actorExpedition->actor_id,
        ]);

        // === BUILD FILES ===
        $subjects = $subjectService->getSubjectCursorForExport($this->actorExpedition->expedition_id);

        $subjects->chunk(1000)->each(function ($chunk) use ($queue) {
            $filesData = $chunk->map(function ($subject) use ($queue) {
                return [
                    'queue_id' => $queue->id, 'subject_id' => (string) $subject->_id,
                    'access_uri' => $subject->accessURI, 'created_at' => now(), 'updated_at' => now(),
                ];
            })->toArray();

            // Bulk upsert (insert if not exists, update if does)
            ExportQueueFile::upsert($filesData, ['queue_id', 'subject_id'], ['access_uri', 'updated_at']);

            return true;  // Continue chunking
        });

        $queue->total = $this->actorExpedition->total;
        $queue->files_ready = 1;
        $queue->save();

        // The service handles the "is another running?" check safely now.
        $queueService->processNextQueue();

    }

    /**
     * Handle a job failure.
     * Sends notification to admin user about the failure.
     *
     * @param  Throwable  $throwable  The exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        $this->notifyGroupOnFailure($this->actorExpedition, $throwable);
    }
}
