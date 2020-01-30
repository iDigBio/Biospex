<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Actor\ActorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ActorJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable;

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
     * ActorJob constructor.
     *
     * @param $actor string
     */
    public function __construct($actor)
    {
        $this->onQueue(config('config.workflow_tube'));
        $this->actor = unserialize($actor);
    }

    /**
     * Handle Job.
     */
    public function handle()
    {
        try
        {
            $actorClass = ActorFactory::create($this->actor->class);
            $actorClass->actor($this->actor);
            $this->delete();
        }
        catch (\Exception $e)
        {
            event('actor.pivot.error', $this->actor);

            $user = User::find(1);
            $message = [
                'Actor:' . $this->actor->id,
                'Expedition: ' . $this->actor->pivot->expedition_id,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();
        }
    }
}

