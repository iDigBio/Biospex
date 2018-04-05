<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Interfaces\Expedition;
use App\Notifications\NfnTranscriptionsComplete;
use App\Notifications\NfnTranscriptionsError;
use App\Services\Actor\ActorServiceConfig;
use App\Services\Api\NfnApi;
use App\Facades\GeneralHelper;

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
     * @var \App\Services\Api\NfnApi
     */
    private $api;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param Expedition $expeditionContract
     * @param ActorServiceConfig $actorServiceConfig
     * @param \App\Services\Api\NfnApi $api
     */
    public function __construct(
        Expedition $expeditionContract,
        ActorServiceConfig $actorServiceConfig,
        NfnApi $api
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->actorServiceConfig = $actorServiceConfig;
        $this->api = $api;
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
            ->findWith($actor->pivot->expedition_id, ['project.group.owner', 'stat', 'nfnWorkflow']);

        if ($this->workflowIdDoesNotExist($record))
        {
            $this->actorServiceConfig->fireActorUnQueuedEvent();

            return;
        }

        try
        {
            $this->api->setProvider();
            $this->api->checkAccessToken('nfnToken');
            $uri = $this->api->getWorkflowUri($record->nfnWorkflow->workflow);
            $request = $this->api->buildAuthorizedRequest('GET', $uri);
            $result = $this->api->sendAuthorizedRequest($request);

            $workflow = $result['workflows'][0];
            $count = $workflow['subjects_count'];
            $transcriptionCompleted = $workflow['classifications_count'];
            $transcriptionTotal = GeneralHelper::transcriptionsTotal($workflow['subjects_count']);
            $percentCompleted = GeneralHelper::transcriptionsPercentCompleted($transcriptionTotal, $transcriptionCompleted);

            $record->stat->subject_count = $count;
            $record->stat->transcriptions_total = $transcriptionTotal;
            $record->stat->transcriptions_completed = $transcriptionCompleted;
            $record->stat->percent_completed = $percentCompleted;
            $record->stat->save();


            if ($workflow['finished_at'] !== null)
            {
                $this->actorServiceConfig->fireActorCompletedEvent();
                $this->notify($record);
            }

            NfnClassificationsUpdateJob::dispatch($record->id);

            $this->actorServiceConfig->fireActorUnQueuedEvent();

            return;
        }
        catch (\Exception $e)
        {
            $this->actorServiceConfig->fireActorErrorEvent();

            $message = trans('errors.nfn_classifications_error', [
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
        $message = trans('emails.nfn_transcriptions_complete_message', ['expedition' => $record->title]);

        $record->project->group->owner->notify(new NfnTranscriptionsComplete($message));
    }

    /**
     * @param $record
     * @return bool
     */
    protected function workflowIdDoesNotExist($record)
    {
        if ($record->nfnWorkflow === null || empty($record->nfnWorkflow->workflow))
        {
            /*
            $this->actorServiceConfig->fireActorUnQueuedEvent();
            $message = trans('errors.missing_nfnworkflow', ['title'   => $record->title]);
            $record->project->group->owner->notify(new NfnTranscriptionsComplete($message));
            */
            return true;
        }

        return false;
    }
}
