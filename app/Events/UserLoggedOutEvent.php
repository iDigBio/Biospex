<?php namespace Biospex\Events;

use Illuminate\Queue\SerializesModels;

class UserLoggedOutEvent extends Event {

	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct()
	{

	}

}
