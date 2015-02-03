<?php

use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;

class TestCommand extends Command {

	/**
	 * The console command name.
	 */
	protected $name = 'test:test';

	/**
	 * The console command description.
	 */
	protected $description = 'Used to test code';

	/**
	 * Constructor
	 */
	public function __construct (
		Dispatcher $events
	)
	{
		parent::__construct();
		$this->events = $events;
	}

	/**
	 * Fire queue.
	 */
	public function fire ()
	{

		$this->events->fire('user.newpassword', [
			'email' => "biospex@gmail.com",
			'newPassword' => "asfdfasasfa"
		]);
		echo "Email fired\n";

		return;
	}

}