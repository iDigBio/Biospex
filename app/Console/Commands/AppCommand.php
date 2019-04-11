<?php

namespace App\Console\Commands;


use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
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
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFile;

    /**
     * AppCommand constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \App\Repositories\Interfaces\OcrFile $ocrFile
     */
    public function __construct(OcrQueue $ocrQueue, OcrFile $ocrFile)
    {
        parent::__construct();
        $this->ocrQueue = $ocrQueue;
        $this->ocrFile = $ocrFile;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $queue = $this->ocrQueue->getOcrQueueForOcrProcessCommand();
        $files = $this->ocrFile->getAllOcrQueueFiles($queue->id);
        $files->each(function($file){
            dd($file->queue_id);
        });
    }
}
