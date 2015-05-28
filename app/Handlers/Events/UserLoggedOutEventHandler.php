<?php namespace Biospex\Handlers\Events;

use Biospex\Events\UserLoggedOutEvent;
use Illuminate\Support\Facades\Session;

class UserLoggedOutEventHandler {

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
        Session::flush();
	}

}
