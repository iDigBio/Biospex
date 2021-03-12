<?php
/*
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

namespace App\Listeners;

use App\Services\Model\ActorService;

/**
 * Class ActorExpeditionPivotEventSubscriber
 *
 * @package App\Listeners
 */
class ActorExpeditionPivotEventSubscriber
{
    /**
     * @var \App\Services\Model\ActorService
     */
    private $actorService;

    /**
     * ActorExpeditionPivotEventSubscriber constructor.
     *
     * @param \App\Services\Model\ActorService $actorService
     */
    public function __construct(ActorService $actorService)
    {
        $this->actorService = $actorService;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'actor.pivot.queued',
            'App\Listeners\ActorExpeditionPivotEventSubscriber@actorPivotQueued'
        );

        $events->listen(
            'actor.pivot.export',
            'App\Listeners\ActorExpeditionPivotEventSubscriber@actorPivotExport'
        );

        $events->listen(
            'actor.pivot.error',
            'App\Listeners\ActorExpeditionPivotEventSubscriber@actorPivotError'
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
        $this->actorService->updateActorExpeditionPivot($actor, $actor->pivot->expedition_id, $attributes);
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
            'queued' => 0,
            'error'  => 1
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }

    /**
     * Generate expedition download.
     *
     * @param $actor
     */
    public function actorPivotExport($actor)
    {
        $attributes = [
            'state'     => $actor->pivot->state,
            'total'     => $actor->pivot->total,
            'queued' => $actor->pivot->queued
        ];
        $this->updateActorExpeditions($actor, $attributes);
    }
}