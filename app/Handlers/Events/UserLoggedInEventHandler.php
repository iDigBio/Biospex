<?php namespace Biospex\Handlers\Events;

use Biospex\Events\UserLoggedInEvent;
use Illuminate\Support\Facades\Session;

class UserLoggedInEventHandler {

    /**
     * Create the event handler.
     */
	public function __construct()
	{

    }

	/**
	 * Handle the event.
	 *
	 * @param  UserLoggedIn  $event
	 * @return void
	 */
	public function handle(UserLoggedInEvent $event)
	{
		Session::put('userId', $event->userId);
        Session::put('email', $event->email);
	}

}
