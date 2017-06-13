<?php

namespace App\Services\Actor;

use App\Services\Queue\ActorQueue;

class ActorFactory
{

    /**
     * @see ActorQueue::fire()
     *
     * @param $actorPath
     * @param $actorClass
     * @return \Illuminate\Foundation\Application|\Laravel\Lumen\Application|mixed
     */
    public static function create($actorPath, $actorClass)
    {
        $classPath = __NAMESPACE__ . '\\' . $actorPath . '\\' . $actorClass;
        return app($classPath);
    }
}