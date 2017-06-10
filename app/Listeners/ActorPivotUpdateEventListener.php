<?php

namespace App\Listeners;

use App\Models\Actor;
use App\Repositories\Contracts\ActorContract;

class ActorPivotUpdateEventListener
{

    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * Create the event listener.
     * @param ActorContract $actorContract
     */
    public function __construct(ActorContract $actorContract)
    {
        $this->actorContract = $actorContract;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'actor.pivot.processed',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotProcessed'
        );

        $events->listen(
            'actor.pivot.queued',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotQueued'
        );

        $events->listen(
            'actor.pivot.unqueued',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotUnQueued'
        );

        $events->listen(
            'actor.pivot.state',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotState'
        );

        $events->listen(
            'actor.pivot.regenerate',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotRegenerate'
        );

        $events->listen(
            'actor.pivot.error',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotError'
        );
    }

    /**
     * Update ActorExpeditions table.
     *
     * @param $actor
     * @param array $attributes
     */
    private function updateActorExpeditions($actor, array $attributes = [])
    {
        $result = $this->actorContract->updateActorExpeditionPivot($actor, $actor->pivot->expedition_id, $attributes);
        $result === 0 ?: $this->actorContract->forgetCache();
    }

    /**
     * Update actor pivot processed for image exports.
     *
     * @param $actor
     */
    public function actorPivotProcessed($actor)
    {
        $attributes = [
            'processed' => $actor->pivot->processed
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
 * Update actor for new queue.
 *
 * @param $actor
 */
    public function actorPivotQueued($actor)
    {
        $attributes = [
            'total'     => $actor->pivot->total,
            'processed' => 0,
            'queued'    => 1
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Update actor to clear queue.
     *
     * @param $actor
     */
    public function actorPivotUnQueued($actor)
    {
        $attributes = [
            'total'     => $actor->pivot->total,
            'processed' => 0,
            'queued'    => 0
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Update actor for new state.
     *
     * @param $actor
     */
    public function actorPivotState($actor)
    {
        $attributes = [
            'state'     => $actor->pivot->state,
            'processed' => 0,
            'queued'    => 0,
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Regenerate expedition download.
     *
     * @param $actor
     * @param $count
     */
    public function actorPivotRegenerate($actor, $count)
    {
        $attributes = [
            'state'     => 0,
            'total'     => $count,
            'processed' => 0,
            'queued'    => 1
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Set error on ActorExpeditions.
     *
     * @param $actor
     */
    public function actorPivotError($actor)
    {
        $attributes = [
            'queued' => 0,
            'error'  => 1
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }
}