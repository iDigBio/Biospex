<?php  namespace App\Services\Queue;

use App\Services\Process\RecordSet;
use App\Services\Report\Report;

class RecordSetImportQueue extends QueueAbstract
{
    /**
     * Url for recordset.
     *
     * @var string
     */
    public $recordsetUrl;

    /**
     * Response from curl.
     *
     * @var object
     */
    public $response;

    /**
     * Import directory.
     *
     * @var string
     */
    public $importDir;

    /**
     * Beanstalkd queue.
     *
     * @var string
     */
    public $queue;

    public function __construct(RecordSet $record, Report $report)
    {
        $this->record = $record;
        $this->report = $report;
    }

    /**
     * Fire method
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try {
            if ($this->record->process($data)) {
                $this->delete();
            }

            return;
        } catch (\Exception $e) {
            $this->report->addError(trans('emails.error_import_process',
                ['id' => $this->data['id'], 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            ));
            $this->report->reportSimpleError();
        }

        return;
    }
}
