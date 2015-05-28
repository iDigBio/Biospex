<?php namespace Biospex\Handlers\Events;

use Biospex\Events\UserLoggedOut;
use Illuminate\Support\Facades\Session;

class UserLoggedOutHandler {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  UserLoggedOut  $event
	 * @return void
	 */
	public function handle(UserLoggedOut $event)
	{
        Session::flush();
	}

}
