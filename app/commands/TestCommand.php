<?php

use Illuminate\Console\Command;

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
	 *
	 * @param OcrQueueInterface $queue
	 * @param SubjectInterface $subject
	 * @param Report $report
	 */
	public function __construct ()
	{
		parent::__construct();
	}

	/**
	 * Fire queue.
	 */
	public function fire ()
	{

	}
}