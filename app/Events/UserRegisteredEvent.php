<?php

namespace App\Events;

class UserRegisteredEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @param $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }
}
