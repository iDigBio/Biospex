<?php

namespace App\Services\Actor\Ocr;

use App\Models\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
use App\Repositories\Interfaces\Subject;
use App\Services\Requests\HttpRequest;
use Artisan;
use GuzzleHttp\Exception\GuzzleException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Storage;
use Exception;

class OcrTesseract extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subject;

    /**
     * @var \thiagoalessio\TesseractOCR\TesseractOCR
     */
    public $tesseract;

    /**
     * @var \App\Services\Requests\HttpRequest
     */
    public $httpRequest;

    /**
     * @var
     */
    private $imgUrl;

    /**
     * @var
     */
    private $imgPath;

    /**
     * OcrTesseract constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \thiagoalessio\TesseractOCR\TesseractOCR $tesseract
     * @param \App\Services\Requests\HttpRequest $httpRequest
     * @param \App\Repositories\Interfaces\Subject $subject
     */
    public function __construct(
        OcrQueue $ocrQueue,
        TesseractOCR $tesseract,
        HttpRequest $httpRequest,
        Subject $subject
    ) {
        $this->tesseract = $tesseract;
        $this->httpRequest = $httpRequest;
        $this->ocrQueue = $ocrQueue;
        $this->subject = $subject;
    }

    /**
     * Process ocr file.
     *
     * @param \App\Models\OcrFile $file
     * @param $folderPath
     */
    public function process(OcrFile $file, $folderPath)
    {
        $this->createImagePath($file, $folderPath);

        if ( ! $this->getImage($file)) {
            $this->updateQueue($file);
            return;
        }

        $this->tesseractImage($file);

        Storage::delete($this->imgPath);

        $this->updateQueue($file);

        return;
    }

    /**
     * Get image.
     *
     * @param \App\Models\OcrFile $file
     * @return bool
     */
    private function getImage(OcrFile $file)
    {
        try {
            if (Storage::exists($this->imgPath)) {
                return true;
            }

            $this->httpRequest->setHttpProvider();
            $this->httpRequest->getHttpClient()->request('GET', $this->imgUrl, ['sink' => Storage::path($this->imgPath)]);

            return true;
        } catch (GuzzleException $e) {
            $file->messages = $e->getMessage();
            $file->ocr = 'error';
            $file->status = 'completed';
            $file->save();

            return false;
        }
    }

    /**
     * Read image.
     *
     * @param \App\Models\OcrFile $file
     */
    private function tesseractImage(OcrFile $file)
    {
        try {
            $result = $this->tesseract->image(Storage::path($this->imgPath))->threadLimit(1)->run();
            $file->ocr = preg_replace('/\s+/', ' ', trim($result));
            $file->status = 'completed';
            $file->save();
        } catch (Exception $e) {
            $file->messages = 'Error occurred performing ocr on the image.';
            $file->ocr = 'error';
            $file->status = 'completed';
            $file->save();
        }
    }

    /**
     * Create paths.
     *
     * @param \App\Models\OcrFile $file
     * @param $folderPath
     */
    private function createImagePath(OcrFile $file, $folderPath)
    {
        $this->imgUrl = str_replace(' ', '%20', $file->url);
        $this->imgPath = $folderPath.'/'.$file->subject_id.'.jpg';
    }


    /**
     * Update queue processed.
     *
     * @param \App\Models\OcrFile $file
     */
    private function updateQueue(OcrFile $file) {
        $queue = $this->ocrQueue->find($file->queue_id);
        $queue->processed = $queue->processed + 1;
        if ($queue->processed === $queue->total) {
            $queue->status = 1;
            $queue->save();
            Artisan::call('ocrprocess:records');

            return;
        }

        $queue->save();
    }
}