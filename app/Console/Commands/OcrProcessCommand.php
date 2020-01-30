<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Repositories\Interfaces\OcrQueue;
use App\Notifications\JobError;
use App\Services\Actor\OcrComplete;
use App\Services\Actor\OcrProcess;
use Artisan;
use Exception;
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
     * @var OcrComplete
     */
    private $ocrComplete;

    /**
     * @var \App\Services\Actor\OcrProcess
     */
    private $ocrProcess;

    /**
     * OcrProcessCommand constructor.
     *
     * OcrProcessCommand constructor.
     *
     * @param OcrQueue $ocrQueueContract
     * @param OcrComplete $ocrComplete
     * @param \App\Services\Actor\OcrProcess $ocrProcess
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        OcrComplete $ocrComplete,
        OcrProcess $ocrProcess
    ) {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrComplete = $ocrComplete;
        $this->ocrProcess = $ocrProcess;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($queue === null) {
            return;
        }

        try {
            if ($queue->status === 1) {
                $this->ocrComplete->process($queue);
                Artisan::call('ocrprocess:records');

                return;
            }

            $this->ocrProcess->process($queue);

            return;
        } catch (Exception $e) {
            event('ocr.error', $queue);

            $messages = [
                $queue->project->title,
                'Error processing ocr record '.$queue->id,
                'File: '.$e->getFile(),
                'Message: '.$e->getMessage(),
                'Line: '.$e->getLine(),
            ];

            $user = User::find(1);
            $user->notify(new JobError(__FILE__, $messages));
        }
    }

}
