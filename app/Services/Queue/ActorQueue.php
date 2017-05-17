<?php

namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\Expedition;
use App\Services\Report\Report;
use Illuminate\Support\Facades\App;
use App\Exceptions\Handler;

class ActorQueue extends QueueAbstract
{

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var Expedition
     */
    protected $expedition;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * ActorQueue constructor.
     *
     * @param Report $report
     * @param Expedition $expedition
     * @param Handler $handler
     */
    public function __construct(Report $report, Expedition $expedition, Handler $handler)
    {
        $this->report = $report;
        $this->expedition = $expedition;
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
            $class = App::make(__NAMESPACE__ . '\\' . $actor->class . '\\' . $actor->class . 'Actor');
            $class->processActor($actor);
        }
        catch (BiospexException $e)
        {
            $actor->pivot->queued = 0;
            $actor->pivot->error = 1;
            $actor->pivot->save();
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
        $record = $this->expedition->with(['project.group.owner'])->find($actor->pivot->expedition_id);

        $this->report->addError(trans('errors.workflow_actor',
            [
                'title' => $record->title,
                'class' => $actor->class,
                'message' => $message
            ]));

        $this->report->reportError($record->project->group->owner->email);
    }
}