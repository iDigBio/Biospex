<?php

namespace App\Services\Actor;

use App\Exceptions\ActorException;
use Event;

abstract class ActorServiceBase
{

    /**
     * @var
     */
    protected $config;

    /**
     * @param ActorServiceConfig $config
     */
    abstract protected function setActorServiceConfig(ActorServiceConfig $config);

    /**
     * @throws ActorException
     */
    protected function checkActorServiceConfig()
    {
        if (empty($this->config)) throw new ActorException('Missing required Actor Service Configuration.');
    }

    /**
     * Fire actor update for processed count.
     */
    public function fireActorProcessedEvent()
    {
        $this->checkActorServiceConfig();

        $this->config->actor->pivot->processed++;
        Event::fire('actor.pivot.processed', $this->config->actor);

        /* TODO figure out how to use subject count so update is not happening each time image is processed
        /* TODO When compressing files,
        $count = null !== $this->subjects ? $this->subjects->count() : $this->actor->pivot->total;
        if ($this->actor->pivot->processed % 25 === 0 || ($count - $this->actor->pivot->processed === 0) )
        {

        }
        */
    }

    /**
     * Fire actor reset event.
     */
    public function fireResetActorEvent()
    {
        $this->checkActorServiceConfig();

        $this->config->actor->pivot->processed = 0;
        $data = [$this->config->actor, $this->config->actor->pivot->total];
        Event::fire('actor.pivot.queued', $data);
    }

    /**
     * Fire actor state event.
     */
    public function fireStateActorEvent()
    {
        $this->checkActorServiceConfig();

        $this->config->actor->pivot->state++;
        Event::fire('actor.pivot.state', $this->config->actor);
    }
}