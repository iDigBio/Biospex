<?php

namespace App\Services\Helpers;

class ActorEventHelper
{
    /**
     * Fire actor update for processed count.
     *
     * @param $actor
     */
    public function fireActorProcessedEvent(&$actor)
    {
        $actor->pivot->processed++;
        event('actor.pivot.processed', $actor);
    }

    /**
     * Fire actor queued event.
     *
     * @param $actor
     */
    public function fireActorQueuedEvent(&$actor)
    {
        $actor->pivot->processed = 0;
        $actor->pivot->queued = 1;

        event('actor.pivot.queued', $actor);
    }

    /**
     * Fire actor unqueued event.
     *
     * @param $actor
     */
    public function fireActorUnQueuedEvent(&$actor)
    {
        $actor->pivot->processed = 0;
        $actor->pivot->queued = 0;

        event('actor.pivot.unqueued', $actor);
    }

    /**
     * Fire actor state event.
     *
     * @param $actor
     */
    public function fireActorStateEvent(&$actor)
    {
        $actor->pivot->state++;
        $actor->pivot->processed = 0;
        $actor->pivot->queued = 0;

        event('actor.pivot.state', $actor);
    }

    /**
     * Fire actor error event.
     *
     * @param null $actor
     */
    public function fireActorErrorEvent(&$actor)
    {
        $actor->queued = 0;
        $actor->error = 1;

        event('actor.pivot.error', $actor);
    }

    /**
     * Fire actor completed event.
     *
     * @param $actor
     */
    public function fireActorCompletedEvent(&$actor)
    {
        $actor->pivot->state++;
        $actor->pivot->queued = 0;
        $actor->pivot->completed = 1;

        event('actor.pivot.completed', $actor);
    }

    /**
     * Shortcut to report stage when an issue occurred.
     *
     * @param $actor
     */
    public function fireActorReportStageEvent(&$actor)
    {
        $actor->pivot->state = 5;
        $actor->pivot->processed = 0;
        $actor->pivot->queued = 0;
        event('actor.pivot.report', $actor);
    }
}