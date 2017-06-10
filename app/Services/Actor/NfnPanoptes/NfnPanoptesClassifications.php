<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\NfnWorkflowContract;
use App\Services\Report\Report;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnPanoptesClassifications
{

    use DispatchesJobs;

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * @var NfnWorkflowContract
     */
    private $nfnWorkflowContract;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param ExpeditionContract $expeditionContract
     * @param NfnWorkflowContract $nfnWorkflowContract
     * @param ActorContract $actorContract
     * @param Report $report
     * @internal param ActorContract $actor
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        NfnWorkflowContract $nfnWorkflowContract,
        ActorContract $actorContract,
        Report $report
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->actorContract = $actorContract;
        $this->report = $report;
        $this->nfnWorkflowContract = $nfnWorkflowContract;
    }

    /**
     * Process current state
     * @param $actor
     *
     */
    public function processActor($actor)
    {
        $check = $this->nfnWorkflowContract->setCacheLifetime(0)
            ->findWhere(['expedition_id', '=', $actor->expedition_id])
            ->isEmpty();
        if ($check)
        {
            // TODO add email to project owner telling them to add the workflow id
            return;
        }

        $record = $this->expeditionContract->setCacheLifetime(0)
            ->with(['project.group.owner', 'stat'])
            ->find($actor->pivot->expedition_id);

        try
        {
            if ((int) $record->stat->percent_completed === 100)
            {
                $actor->pivot->queued = 0;
                $actor->completed = 1;
                $actor->pivot->save();

                $this->sendReport($record);

                return;
            }

            $this->dispatch((new NfnClassificationsUpdateJob([$record->id]))
                ->onQueue(config('config.beanstalkd.classification')));

            $this->actorContract->update(['queued' => 0], $actor->id);

            return;
        }
        catch (BiospexException $e)
        {
            $this->report->addError(trans('errors.nfn_classifications_error', [
                'title'   => $record->title,
                'id'      => $record->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($record->project->group->owner->email);

            $this->service->handler->report($e);
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

}
