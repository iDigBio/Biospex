<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Actor\ActorService;
use App\Services\Report\Report;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnPanoptesClassifications extends NfnPanoptesBase
{

    use DispatchesJobs;

    /**
     * @var ActorService
     */
    private $service;

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
     * NfnPanoptesClassifications constructor.
     *
     * @param ActorService $service
     * @param ExpeditionContract $expeditionContract
     * @param ActorContract $actorContract
     * @param Report $report
     * @internal param ActorContract $actor
     */
    public function __construct(
        ActorService $service,
        ExpeditionContract $expeditionContract,
        ActorContract $actorContract,
        Report $report
    )
    {
        $this->service = $service;
        $this->expeditionContract = $expeditionContract;
        $this->actorContract = $actorContract;
        $this->report = $report;
    }

    /**
     * Process current state
     * @param $actor
     *
     */
    public function process($actor)
    {
        $record = $this->expeditionContract->setCacheLifetime(0)
            ->findWithRelations($actor->pivot->expedition_id, ['project.group.owner', 'stat']);

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
