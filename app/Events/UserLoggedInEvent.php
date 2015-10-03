<?php

namespace App\Events;

class UserLoggedInEvent extends Event
{
    /**
     * @var
     */
    public $userId;
    /**
     * @var
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param $credentials
     */
    public function __construct($credentials)
    {
        $this->userId = $credentials['userId'];
        $this->email = $credentials['email'];
    }
}
