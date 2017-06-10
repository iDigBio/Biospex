<?php

namespace App\Services\Actor;

use App\Exceptions\ActorException;
use Event;

abstract class ActorServiceBase
{

    /**
     * @var ActorServiceConfig
     */
    protected $config;
}