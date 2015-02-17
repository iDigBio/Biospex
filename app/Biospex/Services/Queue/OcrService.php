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
use Biospex\Services\Report\OcrReport;

class OcrService {

	/**
	 * Illuminate\Support\Contracts\MessageProviderInterface
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
	 * Group Id.
	 */
	protected $groupId;

	/**
	 * Group owner email.
	 */
	protected $email;

	/**
	 * Project title
	 */
	protected $title;

	/**
	 * Constructor
	 *
	 * @param OcrQueueInterface $queue
	 * @param SubjectInterface $subject
	 * @param OcrReport $report
	 */
	public function __construct (
		OcrQueueInterface $queue,
		SubjectInterface $subject,
		OcrReport $report
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
		$this->setVars();

		if ( ! $this->checkExist())
			return;

		if ( ! $this->checkError())
			return;

		if ( ! $file = $this->processQueue())
			return;

		if ( ! $this->processFile($file))
			return;

		$csv = $this->updateSubjects($file);

		$attachment = $this->report->complete($this->email, $this->title, $csv);

		!$attachment ? $this->record->destroy($this->record->id) :
			$this->updateRecord(['error' => 1, 'attachments' => json_encode($attachment)]);

		$this->delete();

		return;
	}

	/**
	 * Set variables.
	 */
	private function setVars()
	{
		$this->groupId = $this->record->project->group->id;
		$this->email = $this->record->project->group->owner->email;
		$this->title = $this->record->project->title;

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
	 * @return bool|void
	 */
	private function processQueue ()
	{
		if (empty($this->record->status))
		{
			$this->updateRecord(['status' => 'in progress']);
			if ( ! $this->sendFile())
				return false;

			$this->queueLater();
			return false;
		}

		if ( ! $file = $this->requestFile())
			return false;

		return $file;
	}

	/**
	 * Process returned json file from ocr server. Complete job or queue again for processing.
	 *
	 * @param $file
	 * @return bool
	 */
	private function processFile ($file)
	{
		if (empty($file->header))
		{
			$this->queueLater();
			return false;
		}

		if ($file->header->status == "in progress")
		{
			$this->queueLater();
			return false;
		}

		/**
		TODO - Replace this when Shiva fixes OCR server response.
		if ($file->header->status == "error")
		{
			$this->updateRecord(['error' => 1]);
			$this->addReportError($this->record->id, trans('errors.error_ocr_header'));
			$this->report->reportSimpleError($this->groupId);
			$this->delete();
			return false;
		}
		*/

		return true;
	}

	/**
	 * Update queue record value
	 *
	 * @param $fields
	 */
	private function updateRecord($fields)
	{
		foreach ($fields as $key => $value)
		{
			$this->record->{$key} = $value;
		}

		$this->record->save();
	}

	/**
	 * Update subjects using ocr results.
	 *
	 * @param $file
	 * @return bool
	 */
	private function updateSubjects ($file)
	{
		$csv = [];
		foreach ($file->subjects as $id => $data)
		{
			if ($data->ocr == "error")
			{
				$csv[] = ['id' => $id, 'message' => implode(" -- ", $data->messages), 'url' => $data->url];
				continue;
			}

			$subject = $this->subject->find($id);
			$subject->ocr = $data->ocr;
			$subject->save();
		}

		return $csv;
	}

	/**
	 * Send json data as file.
	 */
	private function sendFile ()
	{
		$delimiter = '-------------' . uniqid();
		$data = '';

		$data .= $this->buildFormContent($delimiter, "response", "text/html", "http");
		$data .= $this->buildFormContent($delimiter, "file", "application/json", $this->record->data, $this->record->uuid . ".json");

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->ocrPostUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: multipart/form-data; boundary=' . $delimiter,
			'Content-Length: ' . strlen($data)));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);

		if ($response === false)
		{
			$this->updateRecord(['error' => 1]);
			$this->addReportError($this->record->id, trans('errors.error_ocr_curl'));
			$this->report->reportSimpleError($this->groupId);
			return false;
		}

		return true;
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
			$this->updateRecord(['error' => 1]);
			$this->addReportError($this->record->id, trans('errors.error_ocr_request'));
			$this->report->reportSimpleError($this->groupId);
			$this->delete();
			return false;
		}

		return json_decode($file);
	}

	/**
	 * Build form fields sent to the ocr server.
	 *
	 * @param $delimiter
	 * @param $name
	 * @param $type
	 * @param $content
	 * @param null $filename
	 * @return string
	 */
	private function buildFormContent($delimiter, $name, $type, $content, $filename = null)
	{
		$data = '';
		$data .= "--" . $delimiter . "\r\n";
		$data .= 'Content-Disposition: form-data; name="' . $name . '";';
		$data .= ! is_null($filename) ? 'filename="' . $filename . '"' : "";
		$data .= "\r\n";
		$data .= 'Content-Type: ' . $type . "\r\n";
		$data .= "\r\n";
		$data .= $content . "\r\n";
		$data .= "\r\n\r\n";
		$data .= "--" . $delimiter . "--\r\n";

		return $data;
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
		$this->updateRecord(['tries' => $this->record->tries +=1]);
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