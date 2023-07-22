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
use App\Notifications\JobError;
use App\Notifications\NfnTranscriptionsComplete;
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
        $this->onQueue(config('config.queues.classification'));
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
            'nfnActor',
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

        // TODO this should set state to value that no longer does anything. Expedition Nfn completed.
        $attributes = [
            'state' => $expedition->nfnActor->pivot->state+1,
        ];

        $expedition->nfnActor()->updateExistingPivot($expedition->nfnActor->pivot->actor_id, $attributes);

        $expedition->project->group->owner->notify(new NfnTranscriptionsComplete($expedition->title));
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
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $user = User::find(1);
        $messages = [
            t('Error: %s', $exception->getMessage()),
            t('File: %s', $exception->getFile()),
            t('Line: %s', $exception->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
