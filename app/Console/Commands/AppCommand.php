<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\OcrQueue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        parent::__construct();
        $this->ocrQueue = $ocrQueue;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $queue = $this->ocrQueue->getOcrQueueForOcrProcessCommand();
        dd($queue->updated_at->addMinutes(15)->lt(Carbon::now()));
    }
}
