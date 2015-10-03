<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class ResetPasswordEvent extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param $email
     * @param $password
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
