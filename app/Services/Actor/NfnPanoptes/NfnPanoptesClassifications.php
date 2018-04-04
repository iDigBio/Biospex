<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Interfaces\Expedition;
use App\Notifications\NfnTranscriptionsComplete;
use App\Notifications\NfnTranscriptionsError;
use App\Services\Actor\ActorServiceConfig;

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
     * NfnPanoptesClassifications constructor.
     *
     * @param Expedition $expeditionContract
     * @param ActorServiceConfig $actorServiceConfig
     */
    public function __construct(
        Expedition $expeditionContract,
        ActorServiceConfig $actorServiceConfig
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->actorServiceConfig = $actorServiceConfig;
    }

    /**
     * Process current state
     * @param $actor
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
            if ((int) $record->stat->percent_completed === 100)
            {
                $this->actorServiceConfig->fireActorCompletedEvent();
                $this->notify($record);

                return;
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
