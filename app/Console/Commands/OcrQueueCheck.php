<?php namespace App\Console\Commands;

use App\Repositories\Contracts\OcrQueue;
use App\Services\Report\Report;

class OcrQueueCheck
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ocrqueue:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check ocr queue table for invalid records";

    /**
     * Class constructor
     *
     * @param OcrQueue $queue
     * @param Report $report
     */
    public function __construct(OcrQueue $queue, Report $report)
    {
        parent::__construct();

        $this->queue = $queue;
        $this->report = $report;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $queues = $this->queue->allWith(['project.group.owner']);

        if (empty($queues)) {
            return;
        }

        foreach ($queues as $queue) {
            $this->report->addError(trans('emails.error_ocr_queue',
                [
                    'id'      => $queue->id,
                    'message' => trans('emails.error_ocr_stuck_queue', ['id' => $queue->id, 'tries' => $queue->tries]),
                    'url'     => ''
                ]));
        }

        $this->report->reportSimpleError();

        return;
    }
}
