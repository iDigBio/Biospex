<?php namespace App\Console\Commands;

use App\Repositories\Contracts\OcrQueue;
use App\Services\Report\Report;
use Illuminate\Console\Command;

class OcrQueueCheckCommand extends Command
{
    public $queue;
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
    public function handle()
    {
        $queues = $this->queue->skipCache()->get();

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
