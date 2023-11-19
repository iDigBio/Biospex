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

use App\Models\User;
use App\Notifications\Generic;
use App\Repositories\SubjectRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DeleteUnassignedSubjectsJob
 *
 * @package App\Jobs
 */
class DeleteUnassignedSubjectsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $projectId;

    /**
     * @var \App\Models\User
     */
    private User $user;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\User $user
     * @param int $projectId
     */
    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @return void
     */
    public function handle(SubjectRepository $subjectRepo): void
    {
        try {
            $cursor = $subjectRepo->deleteUnassignedByProject($this->projectId);
            $cursor->each(function($subject) {
                $subject->delete();
            });

            $attributes = [
                'subject' => t('Delete Unassigned Subjects Complete'),
                'html'    => [
                    t('All unassigned subjects for Project Id %s have been deleted.', $this->projectId)
                ]
            ];

            $this->user->notify(new Generic($attributes));

            $this->delete();
        }
        catch (Exception $e) {
            $attributes = [
                'subject' => t('Delete Unassigned Subjects Error'),
                'html'    => [
                    t('Error: Could not delete unassigned subjects for Project Id %s.', $this->projectId),
                    t('An error occurred while importing the Darwin Core Archive.'),
                    t('File: %s', $e->getFile()),
                    t('Line: %s', $e->getLine()),
                    t('Message: %s', $e->getMessage()),
                    t('The Administration has been notified. If you are unable to resolve this issue, please contact the Administration.'),
                ],
            ];
            $this->user->notify(new Generic($attributes, true));

            $this->delete();
        }
    }
}
