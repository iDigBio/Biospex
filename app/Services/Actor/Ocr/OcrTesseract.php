<?php

namespace App\Services\Actor\Ocr;

use App\Repositories\Interfaces\OcrFile;
use App\Services\Requests\HttpRequest;
use GuzzleHttp\Exception\GuzzleException;
use Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use File;

class OcrTesseract extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    public $ocrFile;

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
    public $folderPath;

    /**
     * @var
     */
    public $file;

    /**
     * OcrTesseract constructor.
     *
     * @param \App\Repositories\Interfaces\OcrFile $ocrFile
     * @param \thiagoalessio\TesseractOCR\TesseractOCR $tesseract
     * @param \App\Services\Requests\HttpRequest $httpRequest
     */
    public function __construct(
        OcrFile $ocrFile,
        TesseractOCR $tesseract,
        HttpRequest $httpRequest
    ) {

        $this->ocrFile = $ocrFile;
        $this->tesseract = $tesseract;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Process the ocr queue record.
     *
     * @param $mongoId
     * @throws \Exception
     */
    public function process($mongoId)
    {
        $this->createDir($mongoId);

        $this->file = $this->ocrFile->find($mongoId)->toArray();

        if ($this->file === null) {
            throw new \Exception(__('Error retrieving ocr record '.$mongoId));
        }

        $this->updateHeader();

        $this->processImages();

        $this->deleteDir();
    }

    /**
     * Process ocr file.
     */
    public function processImages()
    {
        collect($this->file['subjects'])->filter(function ($subject, $key) {
            if ($subject['status'] === 'pending') {
                return true;
            }
            $count = $this->file['header']['processed'] + 1;
            $this->updateHeader($count);

            return false;
        })->each(function ($subject, $key) {

            list($uri, $imagePath) = $this->setUriImagePath($key, $subject['url']);

            if (! $this->getImage($key, $uri, $imagePath)) {
                return;
            }

            $this->tesseractImage($key, $imagePath);
            File::delete($imagePath);
        });

        $this->file['header']['status'] = 'completed';
        $this->ocrFile->update($this->file, $this->file['_id']);
    }

    /**
     * Set uri and image path.
     *
     * @param $key
     * @param $url
     * @return array
     */
    public function setUriImagePath($key, $url)
    {
        $uri = str_replace(' ', '%20', $url);
        $imagePath = $this->folderPath.'/'.$key.'.jpg';

        return [$uri, $imagePath];
    }

    /**
     * Get image.
     *
     * @param $key
     * @param $uri
     * @param $imagePath
     * @return bool
     */
    public function getImage($key, $uri, $imagePath)
    {
        try {
            if (File::exists($imagePath)) {
                return true;
            }

            $this->httpRequest->setHttpProvider();
            $this->httpRequest->getHttpClient()->request('GET', $uri, ['sink' => $imagePath]);

            return true;
        } catch (GuzzleException $e) {

            $newValues = [
                'messages' => $e->getMessage(),
                'ocr'      => 'error',
                'status'   => 'completed',
            ];

            $this->updateFile($key, $newValues);

            return false;
        }
    }

    /**
     * Read image.
     *
     * @param $key
     * @param $imagePath
     */
    public function tesseractImage($key, $imagePath)
    {
        try {
            $result = $this->tesseract->image($imagePath)->threadLimit(1)->run();
            $newValues = [
                'ocr'    => preg_replace('/\s+/', ' ', trim($result)),
                'status' => 'completed',
            ];
            $this->updateFile($key, $newValues);
        } catch (\Exception $e) {
            $newValues = [
                'messages' => 'Error occurred performing ocr on the image.',
                'ocr'      => 'error',
                'status'   => 'completed',
            ];
            $this->updateFile($key, $newValues);
        }
    }

    /**
     * Create record directory.
     *
     * @param $mongoId
     */
    public function createDir($mongoId)
    {
        $this->folderPath = Storage::path('ocr/').$mongoId;
        File::makeDirectory($this->folderPath, 0775, true, true);
    }

    /**
     * Delete record directory.
     */
    public function deleteDir()
    {
        File::deleteDirectory($this->folderPath);
    }

    /**
     * Update ocr file.
     *
     * @param $key
     * @param array $newValues
     */
    public function updateFile($key, array $newValues)
    {
        $this->file['subjects'][$key] = array_merge($this->file['subjects'][$key], $newValues);
        $count = $this->file['header']['processed'] + 1;
        $this->updateHeader($count);
    }

    /**
     * Update header.
     *
     * @param int $count
     */
    public function updateHeader($count = 0)
    {
        $this->file['header']['processed'] = $count;
        $this->ocrFile->update($this->file, $this->file['_id']);
    }
}