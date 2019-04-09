<?php

namespace App\Console\Commands;

use App\Jobs\OcrTesseractJob;
use App\Models\User;
use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
use App\Notifications\JobError;
use App\Services\Actor\Ocr\OcrCheck;
use Carbon\Carbon;
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
     * @var OcrCheck
     */
    private $ocrCheck;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFileContract;

    /**
     * OcrProcessCommand constructor.
     *
     * OcrProcessCommand constructor.
     *
     * @param OcrQueue $ocrQueueContract
     * @param \App\Repositories\Interfaces\OcrFile $ocrFileContract
     * @param OcrCheck $ocrCheck
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        OcrFile $ocrFileContract,
        OcrCheck $ocrCheck
    ) {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrFileContract = $ocrFileContract;
        $this->ocrCheck = $ocrCheck;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $record = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($record === null) {
            return;
        }

        try {
            if (! $record->status && $this->lastUpdate($record)) {
                $files = $this->ocrFileContract->getAllOcrQueueFiles($record->id);
                $files->each(function($file){
                    OcrTesseractJob::dispatch($file);
                });

                //event('ocr.queued', $record);
                //OcrTesseractJob::dispatch($record);

                return;
            }

            $this->ocrCheck->check($record);

            return;
        } catch (\Exception $e) {
            event('ocr.error', $record);

            $messages = [
                $record->project->title,
                'Error processing ocr record '.$record->id,
                'File: ' . $e->getFile(),
                'Message: '.$e->getMessage(),
                'Line: '.$e->getLine(),
            ];

            $user = User::find(1);
            $user->notify(new JobError(__FILE__, $messages));
        }
    }

    /**
     * Check last updated.
     *
     * @param $record
     * @return mixed
     */
    private function lastUpdate($record)
    {
        return $record->updated_at->addMinutes(15)->lt(Carbon::now());
    }
}
