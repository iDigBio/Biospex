<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SendInviteEvent extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param $invite
     */
    public function __construct($invite)
    {
        $this->invite = $invite;
    }
}
