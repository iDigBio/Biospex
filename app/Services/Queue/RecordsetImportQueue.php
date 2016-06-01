<?php namespace App\Services\Queue;

use App\Services\Process\RecordSet;
use App\Services\Report\Report;
use Exception;

class RecordSetImportQueue extends QueueAbstract
{
    /**
     * @var RecordSet
     */
    public $record;

    /**
     * @var Report
     */
    public $report;


    /**
     * RecordSetImportQueue constructor.
     * @param RecordSet $record
     * @param Report $report
     */
    public function __construct(RecordSet $record, Report $report)
    {
        $this->record = $record;
        $this->report = $report;
    }

    /**
     * Fire method
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try {
            $this->record->process($data);
            $this->delete();

            return;
        } catch (Exception $e) {
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $this->data['id'], 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->reportSimpleError();
        }
    }
}
