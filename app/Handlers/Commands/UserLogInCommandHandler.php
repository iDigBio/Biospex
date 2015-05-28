<?php namespace Biospex\Handlers\Commands;

use Biospex\Commands\UserLogInCommand;
use Biospex\Repositories\Contracts\Auth;
use Biospex\Events\UserLoggedIn;
use Illuminate\Support\Facades\Event;

class UserLogInCommandHandler {

    /**
     * @var Auth
     */
    private $auth;

    /**
     * Create the command handler.
     *
     * @param Auth $auth
     */
	public function __construct(Auth $auth)
	{
        $this->auth = $auth;
    }

	/**
	 * Handle the command.
	 *
	 * @param  UserLogInCommand  $command
	 * @return void
	 */
	public function handle(UserLogInCommand $command)
	{
		$result = $this->auth->store($command->request);

        if ($result['success'])
            Event::fire(new UserLoggedIn($result));

        return $result;
	}

}
