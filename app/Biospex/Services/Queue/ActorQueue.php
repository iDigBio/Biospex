<?php

namespace Biospex\Services\Queue;

use Biospex\Services\Report\Report;
use Biospex\Services\Actor\ActorFactory;

class ActorQueue extends QueueAbstract
{
    /**
     * @var Report
     */
    protected $report;
    /**
     * @var ActorFactory
     */
    protected $actorFactory;

    public function __construct(Report $report, ActorFactory $actorFactory)
    {
        $this->report = $report;
        $this->actorFactory = $actorFactory;
    }

    public function fire($job, $data)
    {
        $this->job = $job;
        $actor = unserialize($data);

        try {
            $this->actorFactory->factory($actor);
        } catch (\Exception $e) {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();
            $this->createError($actor->pivot->id, $e);
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