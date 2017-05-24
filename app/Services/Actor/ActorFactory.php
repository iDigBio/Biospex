<?php

namespace App\Services\Actor;

use App\Services\Queue\ActorQueue;

class ActorFactory
{

    /**
     * @param $actor
     * @return \Illuminate\Foundation\Application|\Laravel\Lumen\Application|mixed
     * @see ActorQueue::fire()
     */
    public static function create($actor)
    {
        $classPath = __NAMESPACE__ . '\\' . $actor->class . '\\' . $actor->class;
        return app($classPath);
    }
}