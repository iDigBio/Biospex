<?php

namespace App\Services\Actor\Ocr;

use App\Jobs\OcrTesseractJob;
use App\Models\OcrQueue as Model;
use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\OcrQueue;

class OcrProcess extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueueContract;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFileContract;

    /**
     * OcrProcess constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueueContract
     * @param \App\Repositories\Interfaces\OcrFile $ocrFileContract
     */
    public function __construct(OcrQueue $ocrQueueContract, OcrFile $ocrFileContract)
    {

        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrFileContract = $ocrFileContract;
    }

    /**
     * Process ocr.
     *
     * @param \App\Models\OcrQueue $queue
     */
    public function process(Model $queue)
    {
        $files = $this->ocrFileContract->getAllOcrQueueFiles($queue->id);
        $queue->total = $files->count();
        $queue->processed = 0;
        $queue->save();

        $files->each(function ($file) use ($queue) {
            OcrTesseractJob::dispatch($queue, $file);
        });
    }
}