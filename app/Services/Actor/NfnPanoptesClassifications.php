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

namespace App\Services\Actor;

use App\Facades\ActorEventHelper;
use App\Jobs\AmChartJob;
use App\Services\Model\ExpeditionService;
use App\Notifications\NfnTranscriptionsComplete;
use App\Notifications\NfnTranscriptionsError;
use App\Services\Api\PanoptesApiService;
use Exception;

class NfnPanoptesClassifications extends NfnPanoptesBase
{

    /**
     * @var \App\Services\Model\ExpeditionService
     */
    public $expeditionService;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $panoptesApiService;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     */
    public function __construct(
        ExpeditionService $expeditionService,
        PanoptesApiService $panoptesApiService
    )
    {
        $this->expeditionService = $expeditionService;
        $this->panoptesApiService = $panoptesApiService;
    }

    /**
     * Process current state.
     *
     * @param $actor
     */
    public function processActor($actor)
    {
        $record = $this->expeditionService
            ->findWith($actor->pivot->expedition_id, ['project.group.owner', 'stat', 'panoptesProject']);

        if ($this->workflowIdDoesNotExist($record))
        {
            ActorEventHelper::fireActorUnQueuedEvent($actor);

            return;
        }

        try
        {
            $workflow = $this->panoptesApiService->getPanoptesWorkflow($record->panoptesProject->panoptes_workflow_id);
            $this->panoptesApiService->calculateTotals($workflow, $record->id);
            $record->stat->subject_count = $this->panoptesApiService->getSubjectCount();
            $record->stat->transcriptions_goal = $this->panoptesApiService->getTranscriptionsGoal();
            $record->stat->local_transcriptions_completed = $this->panoptesApiService->getLocalTranscriptionsCompleted();
            $record->stat->transcriptions_completed = $this->panoptesApiService->getTranscriptionsCompleted();
            $record->stat->percent_completed = $this->panoptesApiService->getPercentCompleted();

            $this->checkFinishedAt($record, $workflow, $actor);

            $record->stat->save();

            ActorEventHelper::fireActorUnQueuedEvent($actor);

            AmChartJob::dispatch($record->project_id);

            return;
        }
        catch (Exception $e)
        {
            ActorEventHelper::fireActorErrorEvent($actor);

            $record->project->group->owner->notify(new NfnTranscriptionsError($record->title, $record->id, $e->getMessage()));
        }

    }

    /**
     * Send notification for complete process.
     *
     * @param $record
     */
    protected function notify($record)
    {
        $record->project->group->owner->notify(new NfnTranscriptionsComplete($record->title));
    }

    /**
     * Check if finished_at date and set percentage.
     *
     * @param $record
     * @param $workflow
     * @param $actor
     */
    protected function checkFinishedAt($record, $workflow, &$actor)
    {
        if ($workflow['finished_at'] !== null)
        {
            ActorEventHelper::fireActorCompletedEvent($actor);
            $this->notify($record);
        }
    }

    /**
     * @param $record
     * @return bool
     */
    protected function workflowIdDoesNotExist($record)
    {
        if ($record->panoptesProject === null || empty($record->panoptesProject->panoptes_workflow_id))
        {
            return true;
        }

        return false;
    }
}
