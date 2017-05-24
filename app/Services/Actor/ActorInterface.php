<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\StagedQueue;

interface ActorInterface
{

    /**
     * Process Actor.
     *
     * @param Actor $actor
     */
    public function actor(Actor $actor);

    /**
     * Process queue.
     *
     * @param StagedQueue $queue
     */
    public function queue(StagedQueue $queue);
}