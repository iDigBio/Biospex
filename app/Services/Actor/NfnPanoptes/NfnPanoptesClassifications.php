<?php

namespace App\Services\Actor\NfnPanoptes;

ini_set('memory_limit', '1024M');

use App\Exceptions\BiospexException;
use App\Jobs\NfnClassificationsJob;
use App\Services\Actor\ActorInterface;
use App\Services\Actor\ActorService;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnPanoptesClassifications implements ActorInterface
{

    use DispatchesJobs;

    /**
     * @var ActorService
     */
    private $service;

    /**
     * @var \App\Services\Actor\ActorRepositoryService
     */
    private $actorRepoService;

    /**
     * NfnPanoptesClassifications constructor.
     *
     * @param ActorService $service
     */
    public function __construct(ActorService $service
    )
    {
        $this->service = $service;
        $this->actorRepoService = $service->actorRepoService;
    }

    /**
     * Process current state
     *
     * @param $actor
     * @return mixed|void
     * @throws BiospexException
     */
    public function process($actor)
    {

        $record = $this->actorRepoService->expedition->skipCache()->with(['project.group.owner', 'stat'])->find($actor->pivot->expedition_id);

        try
        {
            $this->processExpeditionRecord($actor, $record);
        }
        catch (BiospexException $e)
        {
            $this->service->report->addError(trans('errors.nfn_classifications_error', [
                'title'   => $record->title,
                'id'      => $record->id,
                'message' => $e->getMessage()
            ]));

            $this->service->report->reportError($record->project->group->owner->email);

            $this->service->handler->report($e);
        }

    }

    /**
     * Process the expedition. Set to completed if transcriptions_completed is 100
     * @param $actor
     * @param $record
     */
    protected function processExpeditionRecord($actor, $record)
    {
        if ((int) $record->stat->transcriptions_completed === 100)
        {
            $actor->pivot->queued = 0;
            ++$actor->pivot->state;
            $actor->completed = 1;
            $actor->pivot->save();

            $this->sendReport($record);
        }
        else
        {
            $this->dispatch((new NfnClassificationsJob($actor->pivot->expedition_id))
                ->onQueue($this->service->config->get('config.beanstalkd.job')));
            $actor->pivot->queued = 0;
            $actor->pivot->save();
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
            'message'        => trans('emails.nfn_transcriptions_complete_message', ['expedition', $record->title]),
            'groupId'        => $record->project->group->id,
            'attachmentName' => ''
        ];

        $this->service->processComplete($vars);
    }

}
