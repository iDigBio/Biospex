<?php

namespace App\Services\Queue;

use App\Repositories\Interfaces\Expedition;
use App\Notifications\WorkflowActorError;
use App\Services\Actor\ActorFactory;

class ActorQueue extends QueueAbstract
{

    /**
     * @var Expedition
     */
    protected $expeditionContract;

    /**
     * ActorQueue constructor.
     *
     * @param Expedition $expeditionContract
     */
    public function __construct(Expedition $expeditionContract)
    {
        $this->expeditionContract = $expeditionContract;
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
            $class = ActorFactory::create($actor->class, $actor->class);
            $class->actor($actor);
        }
        catch (\Exception $e)
        {
            event('actor.pivot.error', $actor);
            $message = $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage();
            $this->notify($actor, $message);
        }

        $this->delete();
    }

    /**
     * Create and send error email.
     *
     * @param $actor
     * @param $message
     */
    public function notify($actor, $message)
    {
        $record = $this->expeditionContract->findWith($actor->pivot->expedition_id, ['project.group.owner']);

        $message = trans('messages.workflow_actor',
            [
                'title' => $record->title,
                'class' => $actor->class,
                'message' => $message
            ]);

        $record->project->group->owner->notify(new WorkflowActorError($message));
    }
}