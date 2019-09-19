<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Interfaces\Expedition;
use App\Notifications\NfnTranscriptionsComplete;
use App\Notifications\NfnTranscriptionsError;
use App\Services\Actor\ActorServiceConfig;
use App\Facades\GeneralHelper;
use App\Services\Api\NfnApiService;

class NfnPanoptesClassifications
{

    /**
     * @var Expedition
     */
    public $expeditionContract;

    /**
     * @var ActorServiceConfig
     */
    public $actorServiceConfig;

    /**
     * @var \App\Services\Api\NfnApiService
     */
    private $nfnApiService;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param Expedition $expeditionContract
     * @param ActorServiceConfig $actorServiceConfig
     * @param \App\Services\Api\NfnApiService $nfnApiService
     */
    public function __construct(
        Expedition $expeditionContract,
        ActorServiceConfig $actorServiceConfig,
        NfnApiService $nfnApiService
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->actorServiceConfig = $actorServiceConfig;
        $this->nfnApiService = $nfnApiService;
    }

    /**
     * Process current state.
     *
     * @param $actor
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processActor($actor)
    {
        $this->actorServiceConfig->setActor($actor);

        $record = $this->expeditionContract
            ->findWith($actor->pivot->expedition_id, ['project.group.owner', 'stat', 'panoptesProject']);

        if ($this->workflowIdDoesNotExist($record))
        {
            $this->actorServiceConfig->fireActorUnQueuedEvent();

            return;
        }

        try
        {
            $workflow = $this->nfnApiService->getPanoptesWorkflow($record->panoptesProject->panoptes_workflow_id);
            $count = $workflow['subjects_count'];
            $transcriptionCompleted = $workflow['classifications_count'];
            $transcriptionTotal = GeneralHelper::transcriptionsTotal($workflow['subjects_count']);
            $percentCompleted = GeneralHelper::transcriptionsPercentCompleted($transcriptionTotal, $transcriptionCompleted);

            $record->stat->subject_count = $count;
            $record->stat->transcriptions_total = $transcriptionTotal;
            $record->stat->transcriptions_completed = $transcriptionCompleted;
            $record->stat->percent_completed = $percentCompleted;

            $this->checkFinishedAt($record, $workflow);

            $record->stat->save();

            $this->actorServiceConfig->fireActorUnQueuedEvent();

            NfnClassificationsUpdateJob::dispatch($record->id);

            return;
        }
        catch (\Exception $e)
        {
            $this->actorServiceConfig->fireActorErrorEvent();

            $message = trans('messages.nfn_classifications_error', [
                'title'   => $record->title,
                'id'      => $record->id,
                'message' => $e->getMessage()
            ]);

            $record->project->group->owner->notify(new NfnTranscriptionsError($message));
        }

    }

    /**
     * Send notification for complete process.
     *
     * @param $record
     */
    protected function notify($record)
    {
        $message = trans('messages.nfn_transcriptions_complete_message', ['expedition' => $record->title]);

        $record->project->group->owner->notify(new NfnTranscriptionsComplete($message));
    }

    /**
     * Check if finished_at date and set percentage.
     *
     * @param $record
     * @param $workflow
     */
    protected function checkFinishedAt(&$record, $workflow)
    {
        if ($workflow['finished_at'] !== null)
        {
            $this->actorServiceConfig->fireActorCompletedEvent();
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
