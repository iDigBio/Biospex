<?php

namespace App\Services\Actor;

class ActorFactory
{
    /**
     * Used to create class for different actors.
     *
     * @param string $class
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function create(string $class)
    {
        return app('App\Services\Actor\\' . $class);
    }
}