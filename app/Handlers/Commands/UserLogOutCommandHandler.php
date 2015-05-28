<?php namespace Biospex\Handlers\Commands;

use Illuminate\Support\Facades\Event;
use Biospex\Commands\UserLogOutCommand;
use Biospex\Repositories\Contracts\Auth;
use Biospex\Events\UserLoggedOut;

class UserLogOutCommandHandler {

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
		//
        $this->auth = $auth;
    }

	/**
	 * Handle the command.
	 *
	 * @param  UserLogOutCommand  $command
	 * @return void
	 */
	public function handle(UserLogOutCommand $command)
	{
        $this->auth->destroy();
        Event::fire(new UserLoggedOut());
	}

}
