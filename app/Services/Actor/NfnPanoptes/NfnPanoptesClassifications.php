<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Actor\ActorServiceConfig;
use App\Services\Report\Report;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnPanoptesClassifications
{

    use DispatchesJobs;

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * @var Report
     */
    public $report;

    /**
     * @var ActorServiceConfig
     */
    public $actorServiceConfig;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param ExpeditionContract $expeditionContract
     * @param ActorServiceConfig $actorServiceConfig
     * @param Report $report
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        ActorServiceConfig $actorServiceConfig,
        Report $report
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->actorServiceConfig = $actorServiceConfig;
        $this->report = $report;
    }

    /**
     * Process current state
     * @param $actor
     *
     */
    public function processActor($actor)
    {
        $this->actorServiceConfig->setActor($actor);

        $record = $this->expeditionContract->setCacheLifetime(0)
            ->with(['project.group.owner', 'stat', 'nfnWorkflow'])
            ->find($actor->pivot->expedition_id);

        if ($this->workflowIdDoesNotExist($record))
        {
            return;
        }

        try
        {
            if ((int) $record->stat->percent_completed === 100)
            {
                $this->actorServiceConfig->fireActorCompletedEvent();
                $this->sendReport($record);

                return;
            }

            $this->dispatch((new NfnClassificationsUpdateJob([$record->id]))
                ->onQueue(config('config.beanstalkd.classification')));

            $this->actorServiceConfig->fireActorUnQueuedEvent();

            return;
        }
        catch (BiospexException $e)
        {
            $this->actorServiceConfig->fireActorErrorEvent();

            $this->report->addError(trans('errors.nfn_classifications_error', [
                'title'   => $record->title,
                'id'      => $record->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($record->project->group->owner->email);
        }

    }

    /**
     * Send report for complete process.
     *
     * @param $record
     */
    protected function sendReport($record)
    {
        $vars = [
            'title'          => $record->title,
            'message'        => trans('emails.nfn_transcriptions_complete_message', ['expedition' => $record->title]),
            'groupId'        => $record->project->group->id,
            'attachmentName' => ''
        ];

        $this->report->processComplete($vars);
    }

    /**
     * @param $record
     * @return bool
     */
    protected function workflowIdDoesNotExist($record)
    {
        if ($record->nfnWorkflow === null || empty($record->nfnWorkflow->workflow))
        {
            $this->actorServiceConfig->fireActorUnQueuedEvent();
            $this->report->addError(trans('errors.missing_nfnworkflow', ['title'   => $record->title]));
            $this->report->reportError($record->project->group->owner->email);

            return true;
        }

        return false;
    }
}
