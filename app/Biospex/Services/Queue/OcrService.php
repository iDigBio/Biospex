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
	 * Queue database record
	 */
	protected $record;

	/**
	 * Id of queue record.
	 */
	protected $id;

	/**
	 * Post url for ocr server.
	 */
	protected $ocrPostUrl;

	/**
	 * Get url for ocr server.
	 */
	protected $ocrGetUrl;

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
		$this->id = $data['id'];

		$this->record = $this->queue->findWith($this->id, ['project.group.owner']);

		if ( ! $this->checkExist())
			return;

		if ( ! $this->checkError())
			return;

		$this->processQueue();

		$this->report->ocrComplete($this->record->project->group->owner->email, $this->record->project->title);

		return;
	}

	/**
	 * Check if queue object is empty and remove from job if necessary.
	 *
	 * @return bool
	 */
	private function checkExist ()
	{
		if (count($this->record))
			return true;

		$this->delete();
		return false;
	}

	/**
	 * Check for error in processing queue.
	 *
	 * @return bool
	 */
	private function checkError()
	{
		if ( ! $this->record->error)
			return true;

		$this->delete();
		return false;
	}

	/**
	 * Process the ocr queue
	 *
	 * @param $queue
	 */
	private function processQueue ()
	{
		if (empty($this->record->status))
		{
			$this->updateRecord('status', 'in progress');
			$this->sendFile();
			$this->queueLater();
			return;
		}

		if ( ! $file = $this->requestFile())
			return;

		$this->processFile($file);

		return;
	}

	/**
	 * Process returned json file from ocr server. Complete job or queue again for processing.
	 *
	 * @param $file
	 */
	private function processFile ($file)
	{
		if ($file->header->status == "in progress" || empty($file->header))
		{
			$this->queueLater();
			return;
		}

		if ($file->header->status == "error")
		{
			$this->updateRecord('error', 1);
			$this->addReportError($this->record->id, trans('errors.error_ocr_header'));
			$this->report->reportSimpleError($this->record->project->group->id);
			$this->delete();
			return;
		}

		$this->updateSubjects($file);

		return;
	}

	/**
	 * Update queue record value
	 *
	 * @param $field
	 * @param $value
	 */
	private function updateRecord($field, $value)
	{
		$this->record->{$field} = $value;
		$this->record->save();
	}

	/**
	 * Update subjects using ocr results.
	 *
	 * @param $file
	 */
	private function updateSubjects ($file)
	{
		$queueError = false;

		foreach ($file->subjects as $id => $data)
		{
			if ($data->status == "error")
			{
				$this->addReportError($id, $data->messages, $data->url);
				$queueError = true;
				continue;
			}

			$subject = $this->subject->find($id);
			$subject->ocr = $data->ocr;
			$subject->save();
		}

		if ($queueError == true)
		{
			$this->updateRecord('error', 1);
			$this->report->reportSimpleError($this->record->project->group->id);
			$this->delete();
			return;
		}

		$this->record->destroy($this->record->id);
		$this->delete();

		return;
	}

	/**
	 * Send json data as file.
	 */
	private function sendFile ()
	{
		$delimiter = '-------------' . uniqid();
		$data = '';
		$data .= "--" . $delimiter . "\r\n";
		$data .= 'Content-Disposition: form-data; name="file";
			' . ' filename="' . $this->record->uuid . '.json"' . "\r\n";
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
			$this->updateRecord('error', 1);
			$this->addReportError($this->record->id, trans('errors.error_ocr_curl'));
			$this->report->reportSimpleError($this->record->project->group->id);
		}
		curl_close($ch);

		return;
	}

	/**
	 * Request file from ocr server.
	 *
	 * @return mixed
	 */
	private function requestFile ()
	{
		$file = @file_get_contents($this->ocrGetUrl . '/' . $this->record->uuid . '.json');
		if ($file === false)
		{
			$this->updateRecord('error', 1);
			$this->addReportError($this->record->id, trans('errors.error_ocr_request'));
			$this->report->reportSimpleError($this->record->project->group->id);
			$this->delete();
			return false;
		}

		return json_decode($file);
	}

	/**
	 * Add error to report.
	 *
	 * @param $id
	 * @param $messages
	 * @param string $url
	 */
	private function addReportError ($id, $messages, $url = '')
	{
		$this->report->addError(trans('errors.error_ocr_queue',
			array(
				'id'      => $id,
				'message' => $messages,
				'url'     => $url
			)));

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
	 * Requeue if ocr process is not finished. Check count and set time for first status check.
	 */
	public function queueLater()
	{
		$minutes = $this->record->tries == 0 ? round(2 * ($this->record->subject_count / 10)) : 2;
		$date = \Carbon::now()->addMinutes($minutes);
		\Queue::later($date, 'Biospex\Services\Queue\OcrService', ['id' => $this->id], 'ocr');
		$this->updateRecord('tries', $this->record->tries +=1);
		$this->delete();

		return;
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