<?php

namespace App\Services\Actor\Ocr;

use App\Models\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
use App\Repositories\Interfaces\Subject;
use App\Services\MongoDbService;
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
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * OcrTesseract constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \thiagoalessio\TesseractOCR\TesseractOCR $tesseract
     * @param \App\Services\Requests\HttpRequest $httpRequest
     * @param \App\Repositories\Interfaces\Subject $subject
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        OcrQueue $ocrQueue,
        TesseractOCR $tesseract,
        HttpRequest $httpRequest,
        Subject $subject,
        MongoDbService $mongoDbService
    ) {
        $this->tesseract = $tesseract;
        $this->httpRequest = $httpRequest;
        $this->ocrQueue = $ocrQueue;
        $this->subject = $subject;
        $this->mongoDbService = $mongoDbService;
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

        if (! $this->getImage($file)) {
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
            $data = [
                'messages' => $e->getMessage(),
                'ocr'      => 'error',
                'status'   => 'completed',
            ];
            $this->updateFile($data, $file->_id);

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
            $data = [
                'ocr'      => preg_replace('/\s+/', ' ', trim($result)),
                'status'   => 'completed',
            ];
            $this->updateFile($data, $file->_id);
        } catch (Exception $e) {
            $data = [
                'messages' => 'Error occurred performing ocr on the image.',
                'ocr'      => 'error',
                'status'   => 'completed',
            ];
            $this->updateFile($data, $file->_id);
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
     * Update file using MongoDbService.
     * Using laravel-mongo package is causing connection errors.
     * @param $attributes
     * @param $id
     */
    private function updateFile($attributes, $id)
    {
        $this->mongoDbService->setCollection('ocr_files');
        $this->mongoDbService->updateOneById($attributes, $id);
    }

    /**
     * Update queue processed.
     *
     * @param \App\Models\OcrFile $file
     */
    private function updateQueue(OcrFile $file)
    {
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