<?php namespace App\Listeners;

use App\Events\UserRegisteredEvent;
use App\Services\Mailer\BiospexMailer;

class UserRegisteredEventListener
{
    /**
     * Create the event handler.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegisteredEvent  $event
     * @return void
     */
    public function handle(UserRegisteredEvent $event)
    {
        $userId = $event->result['userId'];
        $email = $event->result['email'];
        $activationCode = $event->result['activationCode'];
        $activateHtmlLink = link_to_route('auth.activate', 'Click Here', ['id' => $userId, 'code' => urlencode($activationCode)]);
        $activateTextLink = route('auth.activate', ['id' => $userId, 'code' => urlencode($activationCode)]);

        $mailer = new BiospexMailer();
        $mailer->welcome($email, $activateHtmlLink, $activateTextLink);
    }
}
