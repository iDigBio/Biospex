<?php namespace Biospex\Handlers\Events;

use Biospex\Events\UserLoggedIn;
use Illuminate\Support\Facades\Session;

class UserLoggedInHandler {

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
	public function handle(UserLoggedIn $event)
	{
		Session::put('userId', $event->userId);
        Session::put('email', $event->email);
	}

}
