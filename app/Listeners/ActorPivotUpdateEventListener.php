<?php

namespace App\Listeners;

use App\Repositories\Interfaces\Actor;

class ActorPivotUpdateEventListener
{

    /**
     * @var Actor
     */
    private $actorContract;

    /**
     * Create the event listener.
     * @param Actor $actorContract
     */
    public function __construct(Actor $actorContract)
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

        $events->listen(
            'actor.pivot.completed',
            'App\Listeners\ActorPivotUpdateEventListener@actorPivotCompleted'
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
        $this->actorContract->updateActorExpeditionPivot($actor, $actor->pivot->expedition_id, $attributes);
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
            'processed' => $actor->pivot->processed,
            'queued'    => $actor->pivot->queued
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
            'processed' => $actor->pivot->processed,
            'queued'    => $actor->pivot->queued
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
            'processed' => $actor->pivot->processed,
            'queued'    => $actor->pivot->queued
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
            'queued' => $actor->queued,
            'error'  => $actor->error
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Regenerate expedition download.
     *
     * @param $actor
     */
    public function actorPivotRegenerate($actor)
    {
        $attributes = [
            'state'     => $actor->pivot->state,
            'total'     => $actor->pivot->total,
            'processed' => $actor->pivot->processed,
            'queued'    => $actor->pivot->queued
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Set actor completed.
     *
     * @param $actor
     */
    public function actorPivotCompleted($actor)
    {
        $attributes = [
            'state'     => $actor->pivot->state,
            'queued'    => $actor->pivot->queued,
            'completed' => $actor->pivot->completed
        ];

        $this->updateActorExpeditions($actor, $attributes);
    }
}