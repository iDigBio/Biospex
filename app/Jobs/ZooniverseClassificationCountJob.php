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

use App\Models\Actor;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Repositories\ExpeditionRepository;
use App\Services\Api\PanoptesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

/**
 * Class ZooniverseClassificationCountJob
 *
 * @package App\Jobs
 */
class ZooniverseClassificationCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var \App\Models\Actor|null
     */
    private ?Actor $actor;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.classification'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @return void
     */
    public function handle(
        ExpeditionRepository $expeditionRepo,
        PanoptesApiService $panoptesApiService
    ) {
        $expedition = $expeditionRepo->findWith($this->expeditionId, [
            'project.group.owner',
            'stat',
            'zooniverseActor',
            'panoptesProject',
        ]);

        if ($expedition === null || $this->workflowIdDoesNotExist($expedition)) {
            $this->delete();

            return;
        }

        $workflow = $panoptesApiService->getPanoptesWorkflow($expedition->panoptesProject->panoptes_workflow_id);
        $panoptesApiService->calculateTotals($workflow, $expedition->id);

        $expedition->stat->subject_count = $panoptesApiService->getSubjectCount();
        $expedition->stat->transcriptions_goal = $panoptesApiService->getTranscriptionsGoal();
        $expedition->stat->local_transcriptions_completed = $panoptesApiService->getLocalTranscriptionsCompleted();
        $expedition->stat->transcriptions_completed = $panoptesApiService->getTranscriptionsCompleted();
        $expedition->stat->percent_completed = $panoptesApiService->getPercentCompleted();

        $expedition->stat->save();

        $this->checkFinishedAt($expedition, $workflow['finished_at']);

        AmChartJob::dispatch($expedition->project_id);
    }

    /**
     * Check if workflow id exists.
     *
     * @param \App\Models\Expedition $expedition
     * @return bool
     */
    protected function workflowIdDoesNotExist(Expedition $expedition): bool
    {
        if ($expedition->panoptesProject === null || empty($expedition->panoptesProject->panoptes_workflow_id)) {
            return true;
        }

        return false;
    }

    /**
     * Check if finished_at date and set percentage.
     *
     * @param \App\Models\Expedition $expedition
     * @param string|null $finishedAt
     * @return void
     */
    protected function checkFinishedAt(Expedition $expedition, string $finishedAt = null): void
    {
        if ($finishedAt === null) {
            return;
        }

        /**
         * State === 3 means Zooniverse actor completed.
         * @see \App\Services\Actors\Zooniverse\Zooniverse::actor()
         */
        $attributes = [
            'state' => 3,
        ];

        $expedition->zooniverseActor()->updateExistingPivot($expedition->zooniverseActor->pivot->actor_id, $attributes);

        $attributes = [
            'subject' => t('Zooniverse Transcriptions Completed'),
            'html'    => [
                t('The Zooniverse digitization process for "%s" has been completed.', $expedition->title)
            ]
        ];

        $expedition->project->group->owner->notify(new Generic($attributes));
    }

    /**
     * Prevent job overlap using expeditionId.
     *
     * @return \Illuminate\Queue\Middleware\WithoutOverlapping[]
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->expeditionId)];
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $attributes = [
            'subject' => t('Zooniverse Classification Count Job Failed'),
            'html'    => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage())
            ],
        ];

        User::find(config('config.admin.user_id'))->notify(new Generic($attributes));
    }
}
