<?php

namespace App\Exceptions;

use App\Notifications\BiospexExceptionNotification;
use Illuminate\Notifications\Notifiable;

class ExceptionNotify
{
    use Notifiable;

    public $email;

    /**
     * Send notification.
     *
     * @param array $error
     */
    public function sendNotification($error)
    {
        $this->email = config('mail.from.address');
        $this->notify(new BiospexExceptionNotification($error));
    }
}