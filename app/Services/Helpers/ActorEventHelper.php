<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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