<?php

namespace Biospex\Services\Actor;

use Illuminate\Support\Facades\App;

class ActorFactory
{
    public function factory($actor)
    {
        $classPath = __NAMESPACE__ . '\\' . $actor->class . '\\' . $actor->class;
        if ( ! class_exists($classPath)) {
            throw new \Exception(trans('emails.actor_factory_create_error', ['class' => $classPath]));
        }

        $class = App::make($classPath);
        $class->process($actor);
    }
}