<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Notifications\WorkflowActorError;
use App\Repositories\Interfaces\Actor as ActorContract;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ActorJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var \App\Models\Actor
     */
    private $actor;

    /**
     * WorkFlowManagerJob constructor.
     *
     * @param \App\Models\Actor $actor
     */
    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
        $this->onQueue(config('config.beanstalkd.workflow'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Repositories\Interfaces\Actor $actorContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     */
    public function handle(ActorContract $actorContract, Expedition $expeditionContract)
    {
        try
        {
            $actor = $actorContract->find($this->actor->id);
            $classPath = __NAMESPACE__ . '\\' . $actor->class . '\\' . $actor->class;
            $class = app($classPath);
            $class->actor($actor);
            $this->delete();
        }
        catch (\Exception $e)
        {
            event('actor.pivot.error', $actor);
            $message = $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage();
            $this->notify($expeditionContract, $actor, $message);
            $this->delete();
        }
    }

    /**
     * Create and send error email.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param $actor
     * @param $message
     */
    public function notify(Expedition $expeditionContract, $actor, $message)
    {
        $record = $expeditionContract->findWith($actor->pivot->expedition_id, ['project.group.owner']);

        $message = trans('errors.workflow_actor',
            [
                'title' => $record->title,
                'class' => $actor->class,
                'message' => $message
            ]);

        $record->project->group->owner->notify(new WorkflowActorError($message));
    }
}
