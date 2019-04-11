<?php

namespace App\Jobs;

use App\Models\OcrFile;
use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\Interfaces\OcrQueue;
use App\Services\Actor\Ocr\OcrTesseract;
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
     * @var \App\Models\OcrFile
     */
    private $file;

    /**
     * @var \App\Models\OcrQueue
     */
    private $queue;

    /**
     * ocrTesseractJob constructor.
     *
     * @param \App\Models\OcrFile $file
     */
    public function __construct(OcrFile $file)
    {
        $this->file = $file;
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Actor\Ocr\OcrTesseract $ocrTesseract
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @return void
     */
    public function handle(OcrTesseract $ocrTesseract, OcrQueue $ocrQueue)
    {
        $queue = $ocrQueue->find($this->file->queue_id);

        $ocrTesseract->process($this->file, $queue);

        $this->delete();
    }
}
