<?php

namespace App\Services\Actor;

class ActorFactory
{

    /**
     * Used to create class for different actors.
     *
     * @param $actorPath
     * @param $actorClass
     * @return \Illuminate\Foundation\Application
     */
    public static function create($actorPath, $actorClass)
    {
        $classPath = __NAMESPACE__ . '\\' . $actorPath . '\\' . $actorClass;
        return app($classPath);
    }
}