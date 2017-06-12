<?php

namespace App\Listeners;

use App\Events\SendReportEvent;
use App\Services\Mailer\BiospexMailer;

class SendReportEventListener
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
    public function handle(SendReportEvent $event)
    {
        $email = $event->data['email'];
        $subject = $event->data['subject'];
        $view = $event->data['view'];
        $data = $event->data['data'];
        $attachments = $event->data['attachments'];

        $this->mailer->sendReport($email, $subject, $view, $data, $attachments);
    }
}
