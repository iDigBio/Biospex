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

use ActorEventHelper;
use App\Models\Actor;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\JobError;
use App\Notifications\NfnTranscriptionsComplete;
use App\Services\Model\ExpeditionService;
use App\Services\Api\PanoptesApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

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
    private $expeditionId;

    /**
     * @var \App\Models\Actor|null
     */
    private $actor;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param \App\Models\Actor|null $actor
     */
    public function __construct(int $expeditionId, Actor $actor = null)
    {
        $this->expeditionId = $expeditionId;
        $this->actor = $actor;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @return void
     */
    public function handle(
        ExpeditionService $expeditionService,
        PanoptesApiService $panoptesApiService
    ) {

        try {
            $expedition = $expeditionService->findWith($this->expeditionId, [
                'project.group.owner',
                'stat',
                'panoptesProject',
            ]);

            if ($expedition === null) {
                $this->delete();

                return;
            }

            if ($this->workflowIdDoesNotExist($expedition) && $this->actor !== null) {
                $this->actor->pivot->processed = 0;
                $this->actor->pivot->queued = 0;
                $this->actor->save();
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

            $this->checkFinishedAt($expedition, $workflow);

            AmChartJob::dispatch($expedition->project_id);

            $this->delete();
        } catch (Exception $e) {
            $messages = [
                'Message: '.$e->getMessage(),
                'File : '.$e->getFile().': '.$e->getLine(),
            ];

            $user = User::find(1);
            $user->notify(new JobError(__FILE__, $messages));

            $this->delete();
        }
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
     * @param Expedition $expedition
     * @param $workflow
     */
    protected function checkFinishedAt(Expedition $expedition, $workflow)
    {
        if ($this->actor === null) {
            return;
        }

        if ($workflow['finished_at'] !== null) {
            $this->actor->pivot->state++;
            $this->actor->pivot->queued = 0;
            $this->actor->pivot->completed = 1;
            $expedition->project->group->owner->notify(new NfnTranscriptionsComplete($expedition->title));

            return;
        }

        $this->actor->pivot->processed = 0;
        $this->actor->pivot->queued = 0;
        $this->actor->save();
    }
}
