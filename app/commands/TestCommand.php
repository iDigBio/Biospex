<?php

use Illuminate\Console\Command;
use Biospex\Services\Queue\OcrService;
use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Services\Report\OcrReport;

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
		OcrQueueInterface $queue,
		SubjectInterface $subject,
		OcrReport $report
	)
	{
		parent::__construct();

		$this->queue = $queue;
		$this->subject = $subject;
		$this->report = $report;
		$this->ocrPostUrl = \Config::get('config.ocrPostUrl');
		$this->ocrGetUrl = \Config::get('config.ocrGetUrl');
		$this->ocrQueue = \Config::get('config.beanstalkd.ocr');
	}

	/**
	 * Fire queue.
	 */
	public function fire ()
	{
		$this->record = $this->queue->findWith(1, ['project.group.owner']);

		$this->sendFile();

		return;
	}

	private function sendFile ()
	{
		$delimiter = '-------------' . uniqid();
		$data = '';

		$data .= "--" . $delimiter . "\r\n";
		$data .= 'Content-Disposition: form-data; name="response"' . "\r\n";
		$data .= 'Content-Type: text/html' . "\r\n";
		$data .= "\r\n";
		$data .= 'http' . "\r\n";
		$data .= "\r\n\r\n";

		$data .= "--" . $delimiter . "\r\n";
		$data .= 'Content-Disposition: form-data; name="file";
			' . ' filename="checkresponse-1.json"' . "\r\n";
		$data .= 'Content-Type: application/json' . "\r\n";
		$data .= "\r\n";
		$data .= $this->record->data . "\r\n";
		$data .= "--" . $delimiter . "--\r\n";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->ocrPostUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: multipart/form-data; boundary=' . $delimiter,
			'Content-Length: ' . strlen($data)));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);

		$response = curl_exec($ch);
		if ($response === false)
		{
			echo curl_error($ch);
			curl_close($ch);
			die("\nResponse was false\n");
		}

		curl_close($ch);

		die("Response was not false\n");
	}
}