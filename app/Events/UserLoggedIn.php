<?php namespace Biospex\Events;

use Illuminate\Queue\SerializesModels;

class UserLoggedIn extends Event {

	use SerializesModels;
    /**
     * @var
     */
    public $userId;
    /**
     * @var
     */
    public $email;

    /**
     * Create a new event instance.
     *
     * @param $credentials
     */
	public function __construct($credentials)
	{
        $this->userId = $credentials['userId'];
        $this->email = $credentials['email'];
    }

}
