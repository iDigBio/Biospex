<?php namespace Biospex\Listeners;

use Biospex\Events\SendInviteEvent;
use Biospex\Services\Mailer\BiospexMailer;

class SendInviteEventListener
{
    public $mailer;

    /**
     * Create the event listener.
     *
     * @param BiospexMailer $mailer
     */
    public function __construct(BiospexMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event
     * @param SendInviteEvent $event
     * @return mixed
     */
    public function handle(SendInviteEvent $event)
    {
        return $this->mailer->sendInvite($event->data);
    }
}