<?php

use Illuminate\Console\Command;
use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Services\Report\Report;
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
	 *
	 * @param OcrQueueInterface $queue
	 * @param SubjectInterface $subject
	 * @param Report $report
	 */
	public function __construct (
		Report $report,
		Dispatcher $events
	)
	{
		parent::__construct();
		$this->events = $events;
	}

	/**
	 * Fire queue.
	 *
	 * @param $job
	 * @param $data
	 */
	public function fire ()
	{

		$this->events->fire('user.newpassword', [
			'email' => "biospex@gmail.com",
			'newPassword' => "asfdfasasfa"
		]);


		/*
		$data = array(
			'projectTitle' => "Testing Email",
			'mainMessage' => trans('projects.ocr_complete'),
		);
		$subject = trans('emails.ocr_complete');
		$view = 'emails.reportocr';
		*/
		//$this->fireEvent('user.sendreport', 'biospex@gmail.com', $subject, $view, $data);

		return;
	}
	protected function fireEvent ($event, $email, $subject, $data, $attachments = array())
	{
		\Event::fire($event, [
			'email' => $email,
			'subject' => $subject,
			'data' => $data,
			'attachment' => $attachments
		]);
	}
}