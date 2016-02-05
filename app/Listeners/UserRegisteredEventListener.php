<?php namespace Biospex\Listeners;

use Biospex\Events\UserRegisteredEvent;
use Biospex\Services\Mailer\BiospexMailer;

class UserRegisteredEventListener
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
     * Handle the event.
     *
     * @param  SendReportEvent $event
     * @return void
     */
    public function handle(UserRegisteredEvent $event)
    {
        $data = [
            'email' => $event->user->email,
            'id' => $event->user->id,
            'code' => urlencode($event->user->activation_code)
        ];

       $this->mailer->sendWelcome($event->user->email, $data);
    }
}