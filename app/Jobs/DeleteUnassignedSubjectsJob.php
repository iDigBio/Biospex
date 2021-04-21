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
use App\Notifications\JobError;
use App\Notifications\RecordDeleteComplete;
use App\Services\Model\SubjectService;
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
    private $projectId;

    /**
     * @var \App\Models\User
     */
    private $user;

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
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     * @return void
     */
    public function handle(SubjectService $subjectService)
    {
        try {
            $cursor = $subjectService->deleteUnassignedByProject($this->projectId);
            $cursor->each(function($subject) {
                $subject->delete();
            });

            $message = [
                t('All unassigned subjects for project id %s have been deleted.', $this->projectId)
            ];

            $this->user->notify(new RecordDeleteComplete($message));

            $this->delete();
        }
        catch (Exception $e) {
            $message = [
                'Error: ' . t('Could not delete unassigned subjects for project %s', $this->projectId),
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $this->user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}
