<?php namespace Biospex\Services\Queue;

/**
 * OcrService.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Services\Report\Report;

class OcrService {

	/**
	 * Illuminate\Support\Contracts\MessageProviderInterface
	 * @var
	 */
	protected $messages;

	/**
	 * Current job
	 */
	protected $job;

	/**
	 * Variable if error exists
	 */
	protected $error = false;

	/**
	 * Constructor
	 *
	 * @param OcrQueueInterface $queue
	 * @param SubjectInterface $subject
	 * @param Report $report
	 */
	public function __construct (
		OcrQueueInterface $queue,
		SubjectInterface $subject,
		Report $report
	)
	{
		$this->queue = $queue;
		$this->subject = $subject;
		$this->report = $report;
		$this->ocrPostUrl = \Config::get('config.ocrPostUrl');
		$this->ocrGetUrl = \Config::get('config.ocrGetUrl');
	}

	/**
	 * Fire queue.
	 *
	 * @param $job
	 * @param $data
	 */
	public function fire ($job, $data)
	{
		$this->job = $job;

		$queue = $this->queue->find($data['id']);

		if ( ! $this->checkExist($queue))
			return;

		if ( ! $this->checkError($queue))
			return;

		$this->processQueue($queue);

		return;
	}

	/**
	 * Check if queue object is empty and remove from job if necessary.
	 *
	 * @param $queue
	 * @return bool
	 */
	private function checkExist ($queue)
	{
		if ($queue->isEmpty())
		{
			$this->delete();
			return false;
		}

		return true;
	}

	/**
	 * Check for error in processing queue.
	 *
	 * @param $queue
	 * @return bool
	 */
	private function checkError($queue)
	{
		if ($queue->error)
		{
			$error = [
				'id'       => $queue->id,
				'messages' => "Ocr Queue error during process",
				'url'      => ''
			];
			$this->addReportError($error);

			return false;
		}

		return true;
	}

	/**
	 * Process the ocr queue
	 *
	 * @param $queue
	 */
	private function processQueue ($queue)
	{
		if (empty($queue->status))
		{
			$queue->status == "in progress";
			$queue->save();
			$this->sendFile($queue);

			return;
		}

		if ($queue->status == "in progress")
		{
			$file = $this->requestFile($queue);
			$this->processFile($queue, $file);
		}

		return;
	}

	/**
	 * Process returned json file from ocr server. Complete job or release again for processing.
	 *
	 * @param $queue
	 * @param $file
	 */
	private function processFile ($queue, $file)
	{
		if ($file->header->status == "in progress")
		{
			$this->release();

			return;
		}

		$this->updateSubjects($queue, $file);

		return;
	}

	private function updateSubjects ($queue, $file)
	{
		foreach ($file->subjects as $id => $data)
		{
			if ($id->data->status == "error")
			{
				$error = [
					'id'       => $id,
					'messages' => $id->data->messages,
					'url'      => $id->data->url
				];
				$this->addReportError($error);
				continue;
			}

			$subject = $this->subject->find($id);
			$subject->ocr = $id->data->ocr;
			$subject->save();
		}

		if ($this->error)
		{
			$queue->error = 1;
			$queue->save();
			$this->report->reportSimpleError();
			$this->delete();
		}

		$this->queue->destroy($queue->id);
		$this->delete();

		return;
	}

	/**
	 * Send json data as file.
	 *
	 * @param $queue
	 */
	private function sendFile ($queue)
	{
		$delimiter = '-------------' . uniqid();
		$data = '';
		$data .= "--" . $delimiter . "\r\n";
		$data .= 'Content-Disposition: form-data; name="' . $queue->uuid . '.json";
			' . ' filename="' . $queue->uuid . '.json"' . "\r\n";
		$data .= 'Content-Type: application/json' . "\r\n";
		$data .= "\r\n";
		$data .= $queue->data . "\r\n";
		$data .= "--" . $delimiter . "--\r\n";

		$handle = curl_init($this->ocrPostUrl);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_HTTPHEADER, array(
			'Content-Type: multipart/form-data; boundary=' . $delimiter,
			'Content-Length: ' . strlen($data)));
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
		curl_exec($handle);
		curl_close($handle);

		$this->release();

		return;
	}

	private function requestFile ($queue)
	{
		$file = file_get_contents($this->ocrGetUrl . '/' . $queue->uuid . '.json');

		return json_decode($file);
	}

	private function addReportError ($error)
	{
		$this->report->addError(trans('errors.error_ocr_queue',
			array(
				'id'      => $error->id,
				'message' => $error->messages,
				'url'     => $error->url
			)));

		$this->error = true;

		return;
	}

	/**
	 * Delete a job from the queue
	 */
	public function delete ()
	{
		$this->job->delete();
	}

	/**
	 * Release a job back to the queue
	 *
	 * @param int $seconds
	 */
	public function release ($seconds = 60)
	{
		$this->job->release($seconds);
	}

	/**
	 * Return number of attempts on the job
	 */
	public function getAttempts ()
	{
		return $this->job->attempts();
	}

	/**
	 * Get id of job
	 */
	public function getJobId ()
	{
		return $this->job->getJobId();
	}
}