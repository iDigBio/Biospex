<?php

namespace App\Jobs;

use App\Models\OcrFile;
use App\Models\OcrQueue;
use App\Services\Actor\OcrTesseract;
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
    public $timeout = 36000;

    /**
     * @var \App\Models\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Models\OcrFile
     */
    private $file;

    /**
     * OcrTesseractJob constructor.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     * @param \App\Models\OcrFile $file
     */
    public function __construct(OcrQueue $ocrQueue, OcrFile $file)
    {
        $this->ocrQueue = $ocrQueue;
        $this->file = $file;
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\OcrTesseract $ocrTesseract
     * @return void
     */
    public function handle(OcrTesseract $ocrTesseract)
    {
        $ocrTesseract->process($this->file);
        event('ocr.poll');
        $this->delete();
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();
    }

}
