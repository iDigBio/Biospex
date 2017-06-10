<?php namespace App\Console\Commands;

use App\Repositories\Contracts\OcrQueueContract;
use App\Services\Report\Report;
use Illuminate\Console\Command;

class OcrQueueCheckCommand extends Command
{

    /**
     * @var OcrQueueContract
     */
    public $queueContract;

    /**
     * @var Report
     */
    public $report;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ocrqueue:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check ocr queue table for invalid records";

    /**
     * OcrQueueCheckCommand constructor.
     * 
     * @param OcrQueueContract $queueContract
     * @param Report $report
     */
    public function __construct(OcrQueueContract $queueContract, Report $report)
    {
        parent::__construct();

        $this->queueContract = $queueContract;
        $this->report = $report;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $queues = $this->queueContract->setCacheLifetime(0)->findAll();

        if ($queues->isEmpty()) {
            return;
        }

        foreach ($queues as $queue) {
            $this->report->addError(trans('errors.ocr_queue',
                [
                    'id'      => $queue->id,
                    'message' => trans('errors.ocr_stuck_queue', ['id' => $queue->id]),
                    'url'     => ''
                ]));
        }

        $this->report->reportError();
    }
}
