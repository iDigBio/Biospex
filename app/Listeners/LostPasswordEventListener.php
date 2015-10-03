<?php namespace App\Listeners;

use App\Events\LostPasswordEvent;
use App\Services\Mailer\BiospexMailer;

class LostPasswordEventListener
{
    /**
     * Handle the event.
     *
     * @param  LostPasswordEvent  $event
     * @return void
     */
    public function handle(LostPasswordEvent $event)
    {
        $userId = $event->user->getId();
        $resetCode = $event->user->getResetPasswordCode();
        $email = $event->user->email;

        $data = [
            'email' => $email,
            'resetHtmlLink' => link_to_route('admin.reset', 'Click Here', ['id' => $userId, 'code' => urlencode($resetCode)]),
            'resetTextLink' => route('admin.reset', ['id' => $userId, 'code' => urlencode($resetCode)]),
        ];

        $mailer = new BiospexMailer();
        $mailer->forgotPassword('emails.contact', trans('emails.contact_subject'), $data);
    }
}
