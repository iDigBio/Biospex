<?php namespace App\Services\Queue;

use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\Ocr;
use App\Services\Report\OcrReport;

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
     * @var OcrReport
     */
    protected $report;

    /**
     * @var OcrCsv
     */
    protected $ocrCsv;

    /**
     * @var OcrQueue
     */
    protected $ocrQueue;

    /**
     * Constructor
     *
     * @param Ocr $ocrProcess
     * @param OcrCsv $ocrCsv
     * @param OcrReport $report
     * @param OcrQueue $ocrQueue
     */
    public function __construct(
        Ocr $ocrProcess,
        OcrCsv $ocrCsv,
        OcrReport $report,
        OcrQueue $ocrQueue
    ) {
        $this->ocrProcess = $ocrProcess;
        $this->ocrCsv = $ocrCsv;
        $this->report = $report;
        $this->ocrQueue = $ocrQueue;
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

        $record = $this->ocrProcess->getOcrQueueRecord($data['id'], ['project.group.owner', 'ocrCsv']);

        $this->processRecord($record);
    }

    private function processRecord($record)
    {
        if ($this->checkExistsAndError($record)) {
            return;
        }

        try {
            if (empty($record->status)) {
                $this->ocrProcess->sendOcrFile($record);
                $record->status = 'in progress';
                $this->queueLater($record);

                return;
            }

            $file = $this->ocrProcess->requestOcrFile($record->uuid);

            if ( ! $this->ocrProcess->checkOcrFileHeaderExists($file)) {
                $this->queueLater($record);

                return;
            }

            if ( ! $this->ocrProcess->checkOcrFileInProgress($file)) {
                $this->queueLater($record, $file);

                return;
            }

            if ( ! $this->ocrProcess->checkOcrFileError($file)) {
                $record->error = 1;
                $this->updateRecord($record);
                $this->addReportError($record->id, trans('emails.error_ocr_header'));
                $this->report->reportSimpleError($record->project->group->id);
                $this->delete();

                return;
            }

            $csv = $this->ocrProcess->updateSubjectsFromOcrFile($file);

            $this->setCsvAttachmentArray($record, $csv);

            $attachment = $this->sendReport($record, $csv);

            $this->updateOrDestroyRecord($record, $attachment);

            if ($record->batch)
            {
                $this->ocrCsv->destroy($record->ocrCsv->id);
            }

            $this->delete();

            return;
        } catch (\Exception $e) {
            $record->error = 1;
            $this->updateRecord($record);
            $this->addReportError($record->id, $e->getTraceAsString());
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
     */
    private function updateRecord($record)
    {
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
     * @param bool $file
     */
    private function queueLater($record, $file = false)
    {
        $record->subject_remaining = $this->ocrProcess->calculateSubjectRemaining($record, $file);
        $record->tries = $record->tries += 1;
        $this->updateRecord($record);
        $count = $this->ocrProcess->getSubjectRemainingSum($record);
        $date = $this->ocrProcess->setQueueLaterTime($count);
        $this->ocrProcess->queueLater($date, $this->data);

        $this->delete();
    }

    /**
     * @param $attachment
     * @param $record
     */
    private function updateOrDestroyRecord($record, $attachment)
    {
        if ( ! $attachment) {
            $record->destroy($record->id);
        } else {
            $this->updateRecord($record, ['error' => 1, 'attachments' => json_encode($attachment)]);
        }
    }

    /**
     * Add csv array to database if batch needs attachment
     * @param $csv
     * @param $record
     */
    protected function setCsvAttachmentArray($record, &$csv)
    {
        $subjects = ! empty($record->ocrCsv->subjects) ? json_decode($record->ocrCsv->subjects, true) : [];
        $csv = array_merge($subjects, $csv);
        $record->ocrCsv->subjects = json_encode($csv);
        $record->ocrCsv->save();

        return;
    }

    /**
     * Send report for completed ocr process
     * @param $record
     * @param $csv
     * @return array|bool
     */
    protected function sendReport($record, &$csv)
    {
        if ($record->batch)
        {
            $email = $record->project->group->owner->email;
            $title = $record->project->title;
            $attachment = $this->report->complete($email, $title, $csv);

            return $attachment;
        }

        return false;
    }
}
