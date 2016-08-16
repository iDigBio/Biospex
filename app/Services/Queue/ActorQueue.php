<?php

namespace App\Services\Queue;

use App\Services\Report\Report;
use App\Services\Actor\ActorFactory;
use Exception;

class ActorQueue extends QueueAbstract
{
    /**
     * @var Report
     */
    protected $report;

    /**
     * ActorQueue constructor.
     * 
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Fire the job.
     * 
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $actor = unserialize($data);

        try {
            ActorFactory::create($actor);
        } catch (Exception $e) {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();
            $this->createError($actor->pivot->expedition_id, $e);
        }

        $this->delete();
    }

    /**
     * Create and send error email
     *
     * @param $id
     * @param $e
     * @internal param $manager
     * @internal param $actor
     */
    public function createError($id, $e)
    {
        $this->report->addError(trans('emails.error_workflow_actor',
            [
                'pivot_id' => $id,
                'error' => $e->getFile() . " - " . $e->getLine() . ": " . $e->getMessage()
            ]));
        $this->report->reportSimpleError();
    }
}