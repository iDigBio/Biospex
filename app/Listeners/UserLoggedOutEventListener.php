<?php namespace App\Listeners;

use App\Events\UserLoggedOutEvent;

class UserLoggedOutEventListener
{
    /**
     * Create the event handler.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  UserLoggedOut  $event
     * @return void
     */
    public function handle(UserLoggedOutEvent $event)
    {
        \Session::flush();
    }
}
