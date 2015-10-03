<?php namespace App\Listeners;

use App\Events\SendInviteEvent;
use App\Services\Mailer\BiospexMailer;

class SendInviteEventListener
{
    /**
     * Handle the event.
     *
     * @param  SendInviteEvent  $event
     * @return void
     */
    public function handle(SendInviteEvent $event)
    {
        $mailer = new BiospexMailer();
        $mailer->sendInvite($event->invite['email'], $event->invite['subject'], $event->invite['view'], $event->invite['data']);
    }
}
