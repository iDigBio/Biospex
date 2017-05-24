<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\StagedQueue;

abstract class ActorBase implements ActorInterface
{
    /**
     * @inheritdoc
     */
    public function actor(Actor $actor)
    {

    }

    /**
     * @inheritdoc
     */
    public function queue(StagedQueue $queue)
    {

    }

}