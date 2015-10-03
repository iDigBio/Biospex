<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class LostPasswordEvent extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
