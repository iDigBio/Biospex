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

use App\Models\Project;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class DeleteUnassignedSubjectsJob
 */
class DeleteUnassignedSubjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 7200; // 2 hours for large datasets

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2; // Reduced for large imports

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user, protected Project $project)
    {
        $this->user = $user->withoutRelations();
        $this->project = $project->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(MongoDbService $mongoDbService): void
    {
        // Set the subjects collection
        $mongoDbService->setCollection('subjects');

        // Get count before deletion for reporting
        $criteria = [
            'project_id' => $this->project->id,
            'expedition_ids' => ['$size' => 0],  // MongoDB syntax for array size
        ];

        $countBeforeDelete = $mongoDbService->count($criteria);

        // Perform bulk delete - much faster than individual deletes
        $mongoDbService->deleteMany($criteria);

        $attributes = [
            'subject' => t('Delete Unassigned Subjects Complete'),
            'html' => [
                t('All unassigned subjects for Project Id %s have been deleted.', $this->project->id),
                t('Total subjects deleted: %d', $countBeforeDelete),
            ],
        ];

        $this->user->notify(new Generic($attributes));

        $this->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Delete Unassigned Subjects Error'),
            'html' => [
                t('Error: Could not delete unassigned subjects for Project Id %s.', $this->project->id),
                t('An error occurred while importing the Darwin Core Archive.'),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
                t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
            ],
        ];
        $this->user->notify(new Generic($attributes, true));
    }
}
