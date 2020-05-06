<?php

namespace App\Jobs;

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\OcrService;
use App\Services\Process\TesseractService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrTesseractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 172800;

    /**
     * @var \App\Models\OcrQueue
     */
    private $ocrQueue;

    /**
     * OcrTesseractJob constructor.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Execute tesseract job.
     *
     * @param \App\Services\Process\OcrService $service
     * @param \App\Services\Process\TesseractService $tesseract
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle(OcrService $service, TesseractService $tesseract)
    {
        $service->setDir($this->ocrQueue->id);

        $count = $service->getSubjectCount($this->ocrQueue->project_id, $this->ocrQueue->expedition_id);
        if ($count === 0) {
            $service->complete($this->ocrQueue);

            \Artisan::call('ocrprocess:records');

            $this->delete();

            return;
        }

        event('ocr.reset', [$this->ocrQueue, $count]);

        $files = $service->getSubjectsToProcess($this->ocrQueue->project_id, $this->ocrQueue->expedition_id);

        foreach ($files as $file) {
            $tesseract->process($file, $service->folderPath);
            $this->ocrQueue->processed = $this->ocrQueue->processed + 1;
            $this->ocrQueue->save();
        }

        event('ocr.status', [$this->ocrQueue]);

        \Artisan::call('ocrprocess:records');

        $this->delete();

        return;
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        event('ocr.error', $this->ocrQueue);

        $messages = [
            $this->ocrQueue->project->title,
            'Error processing ocr record '.$this->ocrQueue->id,
            'File: '.$exception->getFile(),
            'Message: '.$exception->getMessage(),
            'Line: '.$exception->getLine(),
        ];

        $user = User::find(1);
        $user->notify(new JobError(__FILE__, $messages));

        \Artisan::call('ocrprocess:records');
    }
}
