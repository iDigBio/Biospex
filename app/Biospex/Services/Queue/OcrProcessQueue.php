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

class OcrProcessQueue extends QueueAbstract
{
    /**
     * Illuminate\Support\Contracts\MessageProviderInterface
     */
    protected $messages;

    /**
     * Queue database record
     */
    protected $record;

    /**
     * Id of queue record.
     */
    protected $id;

    /**
     * Retruned file array.
     *
     * @var json array
     */
    protected $file;

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
     * Configuration value for queue.
     *
     * @var
     */
    protected $ocrQueue;

    /**
     * Constructor
     *
     * @param OcrQueueInterface $queue
     * @param SubjectInterface $subject
     * @param OcrReport $report
     */
    public function __construct(
        OcrQueueInterface $queue,
        SubjectInterface $subject,
        OcrReport $report
    ) {
        $this->queue = $queue;
        $this->subject = $subject;
        $this->report = $report;
        $this->ocrPostUrl = \Config::get('config.ocrPostUrl');
        $this->ocrGetUrl = \Config::get('config.ocrGetUrl');
        $this->ocrQueue = \Config::get('config.beanstalkd.ocr');
    }

    /**
     * Fire queue.
     *
     * @param $job
     * @param $data
     * @return mixed
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $this->record = $this->queue->findWith($this->data['id'], ['project.group.owner']);

        $this->setVars();

        if (! $this->checkExist()) {
            return;
        }

        if (! $this->checkError()) {
            return;
        }

        try {
            $result = empty($this->record->status) ? $this->sendFile() : $this->requestFile();
            if (! $result) {
                return;
            }

            if (! $this->processFile()) {
                return;
            }

            $csv = $this->updateSubjects();

            $attachment = $this->report->complete($this->email, $this->title, $csv);

            if (! $attachment) {
                $this->record->destroy($this->record->id);
            } else {
                $this->updateRecord(['error' => 1, 'attachments' => json_encode($attachment)]);
            }

            $this->delete();

            return;
        } catch (\Exception $e) {
            $this->updateRecord(['error' => 1]);
            $this->addReportError($this->record->id, $e->getMessage());
            $this->report->reportSimpleError($this->groupId);
            $this->delete();

            return;
        }
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
     * @throws \Exception
     */
    private function checkExist()
    {
        if (count($this->record)) {
            return true;
        }

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
        if (! $this->record->error) {
            return true;
        }

        return false;
    }

    /**
     * Process returned json file from ocr server. Complete job or queue again for processing.
     *
     * @return bool
     */
    private function processFile()
    {
        if (empty($this->file->header)) {
            $this->queueLater();

            return false;
        }

        if ($this->file->header->status == "in progress") {
            $this->queueLater();

            return false;
        }

        if ($this->file->header->status == "error") {
            $this->updateRecord(['error' => 1]);
            $this->addReportError($this->record->id, trans('emails.error_ocr_header'));
            $this->report->reportSimpleError($this->groupId);
            $this->delete();

            return false;
        }


        return true;
    }

    /**
     * Update queue record value
     *
     * @param $fields
     */
    private function updateRecord($fields)
    {
        foreach ($fields as $key => $value) {
            $this->record->{$key} = $value;
        }

        $this->record->save();
    }

    /**
     * Update subjects using ocr results.
     *
     * @return array
     */
    private function updateSubjects()
    {
        $csv = [];
        foreach ($this->file->subjects as $id => $data) {
            if ($data->ocr == "error") {
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
     *
     * @return bool
     * @throws \Exception
     */
    private function sendFile()
    {
        $data = '';
        $delimiter = '-------------' . uniqid();

        $data .= "--" . $delimiter . "\r\n";
        $data .= 'Content-Disposition: form-data; name="response"' . "\r\n\r\n";
        $data .= 'http' . "\r\n";
        $data .= "\r\n";

        $data .= "--" . $delimiter . "\r\n";
        $data .= 'Content-Disposition: form-data; name="file"; filename="' . $this->record->uuid . '.json"' . "\r\n";
        $data .= 'Content-Type: application/json' . "\r\n";
        $data .= "\r\n";
        $data .= $this->record->data . "\r\n";
        $data .= "--" . $delimiter . "--\r\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ocrPostUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data; boundary=' . $delimiter,
            'Content-Length: ' . strlen($data)]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception(trans('emails.error_ocr_curl', ['id' => $this->record->id, 'message' => print_r($response, true)]));
        }

        $this->updateRecord(['status' => 'in progress']);
        $this->queueLater();

        return false;
    }

    /**
     * Request file from ocr server.
     *
     * @return bool
     * @throws \Exception
     */
    private function requestFile()
    {
        $file = @file_get_contents($this->ocrGetUrl . '/' . $this->record->uuid . '.json');
        if ($file === false) {
            throw new \Exception(trans('emails.error_ocr_request', ['id' => $this->record->id]));
        }

        $this->file = json_decode($file);

        return true;
    }

    /**
     * Add error to report.
     *
     * @param $id
     * @param $messages
     * @param string $url
     */
    private function addReportError($id, $messages, $url = '')
    {
        $this->report->addError(trans('emails.error_ocr_queue',
            [
                'id'      => $id,
                'message' => $messages,
                'url'     => ! empty($url) ? $url : ''
            ]));

        return;
    }

    /**
     * Requeue if ocr process is not finished. Check count and set time for first status check.
     */
    public function queueLater()
    {
        $minutes = $this->record->tries == 0 ? round($this->record->subject_count / 15) : 2;
        $date = \Carbon::now()->addMinutes($minutes);
        \Queue::later($date, 'Biospex\Services\Queue\QueueFactory', $this->data, $this->ocrQueue);
        $this->updateRecord(['tries' => $this->record->tries += 1]);
        $this->delete();

        return;
    }
}
