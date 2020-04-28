<?php

namespace App\Console\Commands;

use App\Jobs\OcrTesseractJob;
use App\Repositories\Interfaces\OcrQueue;
use Illuminate\Console\Command;

class OcrProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrprocess:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts ocr processing if queues exist.';

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueueContract;

    /**
     * OcrProcessCommand constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueueContract
     */
    public function __construct(OcrQueue $ocrQueueContract) {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($queue === null || $queue->status === 1) {
            return;
        }

        OcrTesseractJob::dispatch($queue);
    }
}
