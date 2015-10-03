<?php namespace App\Listeners;

use App\Events\ResetPasswordEvent;
use App\Services\Mailer\BiospexMailer;

class ResetPasswordEventListener
{
    /**
     * Handle the event.
     *
     * @param  ResetPasswordEvent  $event
     * @return void
     */
    public function handle(ResetPasswordEvent $event)
    {
        $mailer = new BiospexMailer();
        $mailer->newPassword($event->email, $event->password);
    }
}
