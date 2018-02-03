<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\OcrQueue;
use App\Notifications\JobError;
use App\Services\Process\OcrProcess;
use Artisan;
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
     * @var OcrProcess
     */
    private $ocrProcess;

    /**
     * OcrProcessCommand constructor.
     *
     * OcrProcessCommand constructor.
     * @param OcrQueue $ocrQueueContract
     * @param OcrProcess $ocrProcess
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        OcrProcess $ocrProcess
    )
    {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrProcess = $ocrProcess;
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

        try
        {
            $this->ocrProcess->process($record);
        }
        catch (\Exception $e)
        {
            $record->error = 1;
            $record->save();

            $messages = [
                $record->project->title,
                'Error processing ocr record ' . $record->id,
                'Message: ' . $e->getMessage(),
                'Line: ' . $e->getLine()
            ];

            $record->project->group->owner->notify(new JobError(__FILE__, $messages));
        }

        Artisan::call('ocr:poll');
    }
}
