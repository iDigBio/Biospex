<?php

namespace App\Console\Commands;

use App\Interfaces\OcrQueue;
use App\Jobs\OcrProcessJob;
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
    protected $description = 'Polls Ocr server for file status and fires polling event';

    /**
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * OcrProcessCommand constructor.
     *
     * @param OcrQueue $ocrQueueContract
     */
    public function __construct(OcrQueue $ocrQueueContract)
    {
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
        $record = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();
        if ($record === null)
        {
            return;
        }

        OcrProcessJob::dispatch($record);
    }
}
