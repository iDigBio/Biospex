<?php

namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Actor\ActorFactory;
use App\Services\Report\Report;
use App\Exceptions\Handler;
use Event;

class ActorQueue extends QueueAbstract
{

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var ExpeditionContract
     */
    protected $expeditionContract;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * ActorQueue constructor.
     *
     * @param Report $report
     * @param ExpeditionContract $expeditionContract
     * @param Handler $handler
     */
    public function __construct(Report $report, ExpeditionContract $expeditionContract, Handler $handler)
    {
        $this->report = $report;
        $this->expeditionContract = $expeditionContract;
        $this->handler = $handler;
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

        try
        {
            $class = ActorFactory::create($actor);
            $class->actor($actor);
        }
        catch (BiospexException $e)
        {
            Event::fire('actor.pivot.error', $actor);
            $this->createError($actor, $e->getMessage());
            $this->handler->report($e);
        }

        $this->delete();
    }

    /**
     * Create and send error email.
     *
     * @param $actor
     * @param $message
     */
    public function createError($actor, $message)
    {
        $record = $this->expeditionContract->with(['project.group.owner'])->find($actor->pivot->expedition_id);

        $this->report->addError(trans('errors.workflow_actor',
            [
                'title' => $record->title,
                'class' => $actor->class,
                'message' => $message
            ]));

        $this->report->reportError($record->project->group->owner->email);
    }
}