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

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Subject\SubjectService;
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
     * Create a new job instance.
     */
    public function __construct(protected User $user, protected Project $project)
    {
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(SubjectService $subjectService): void
    {
        try {
            $cursor = $subjectService->deleteUnassignedByProject($this->project->id);
            $cursor->each(function ($subject) {
                $subject->delete();
            });

            $attributes = [
                'subject' => t('Delete Unassigned Subjects Complete'),
                'html' => [
                    t('All unassigned subjects for Project Id %s have been deleted.', $this->project->id),
                ],
            ];

            $this->user->notify(new Generic($attributes));

            $this->delete();
        } catch (Throwable $throwable) {
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

            $this->delete();
        }
    }
}
