<?php

namespace App\Services\Actor;

use Illuminate\Support\Facades\App;

class ActorFactory
{
    public static function create($actor)
    {
        $classPath = __NAMESPACE__ . '\\' . $actor->class . '\\' . $actor->class . 'Actor';
        $class = App::make($classPath);
        $class->process($actor);
    }
}