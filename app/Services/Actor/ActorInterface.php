<?php

namespace App\Services\Actor;

interface ActorInterface
{
    /**
     * Each actor has a process to handle the states.
     *
     * @param $actor
     * @return mixed
     */
    public function process($actor);
}