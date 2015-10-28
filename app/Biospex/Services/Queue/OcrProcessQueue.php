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

use Biospex\Services\Process\Ocr;
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
     * @var Ocr
     */
    protected $ocrProcess;

    /**
     * Constructor
     *
     * @param Ocr $ocrProcess
     * @param OcrQueueInterface $queue
     * @param SubjectInterface $subject
     * @param OcrReport $report
     */
    public function __construct(
        Ocr $ocrProcess,
        OcrReport $report
    ) {
        $this->ocrProcess = $ocrProcess;
        $this->report = $report;
    }

    /**
     * Fire queue
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        $record = $this->ocrProcess->getOcrQueueRecord($data['id'], ['project.group.owner']);

        if ($this->checkExistsAndError($record)) {
            return;
        }

        try {
            if (empty($record->status)) {
                $this->ocrProcess->sendOcrFile($record);
                $this->updateRecord($record, ['status' => 'in progress']);
                $this->queueLater($record);

                return;
            }

            $file = $this->ocrProcess->requestOcrFile($record->uuid);

            if ( ! $this->ocrProcess->checkOcrFileHeaderExists($file)) {
                $this->queueLater($record);

                return;
            }

            if ( ! $this->ocrProcess->checkOcrFileInProgress($file)) {
                $this->queueLater($record);

                return;
            }

            if ( ! $this->ocrProcess->checkOcrFileError($file)) {
                $this->updateRecord($record, ['error' => 1]);
                $this->addReportError($record->id, trans('emails.error_ocr_header'));
                $this->report->reportSimpleError($record->project->group->id);
                $this->delete();

                return;
            }

            $csv = $this->ocrProcess->updateSubjectsFromOcrFile($file);

            $email = $record->project->group->owner->email;
            $title = $record->project->title;
            $attachment = $this->report->complete($email, $title, $csv);
            $this->updateOrDestroyRecord($attachment, $record);

            $this->delete();

            return;
        } catch (\Exception $e) {
            $this->updateRecord($record, ['error' => 1]);
            $this->addReportError($record->id, $e->getMessage());
            $this->report->reportSimpleError($record->project->group->id);
            $this->delete();

            return;
        }
    }

    /**
     * Check if record exists or if record is in error state
     *
     * @param $record
     * @return bool
     */
    private function checkExistsAndError($record)
    {
        if (empty($record)) {
            $this->delete();

            return true;
        }

        if ($record->error) {
            return true;
        }

        return false;
    }

    /**
     * Update queue record value
     *
     * @param $record
     * @param $fields
     */
    private function updateRecord($record, $fields)
    {
        foreach ($fields as $key => $value) {
            $record->{$key} = $value;
        }

        $record->save();
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
     * Requeue if ocr process is not finished. Check count and set time for first status check
     *
     * @param $record
     */
    private function queueLater($record)
    {
        $seconds = $record->tries == 0 ? round(($record->subject_count / 15) * 60) : 120;
        $this->updateRecord($record, ['tries' => $record->tries += 1]);
        $this->release($seconds);
    }

    /**
     * @param $attachment
     * @param $record
     */
    private function updateOrDestroyRecord($attachment, $record)
    {
        if ( ! $attachment) {
            $record->destroy($record->id);
        } else {
            $this->updateRecord($record, ['error' => 1, 'attachments' => json_encode($attachment)]);
        }
    }
}
