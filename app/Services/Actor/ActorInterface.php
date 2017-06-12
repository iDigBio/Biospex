<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\ExportQueue;

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
     * @param ExportQueue $queue
     */
    public function queue(ExportQueue $queue);
}