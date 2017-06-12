<?php

namespace App\Listeners;

use App\Events\SendErrorEvent;
use App\Services\Mailer\BiospexMailer;

class SendErrorEventListener
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
     * @param  SendErrorEvent $event
     * @return void
     */
    public function handle(SendErrorEvent $event)
    {
        $email = $event->data['email'];
        $subject = $event->data['subject'];
        $view = $event->data['view'];
        $data = $event->data['data'];
        $attachments = $event->data['attachments'];

        $this->mailer->sendError($email, $subject, $view, $data, $attachments);
    }
}
