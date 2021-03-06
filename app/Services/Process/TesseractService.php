<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Process;

use App\Services\Model\SubjectService;
use App\Services\Requests\HttpRequest;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * Class TesseractService
 *
 * @package App\Services\Process
 */
class TesseractService
{

    /**
     * @var \thiagoalessio\TesseractOCR\TesseractOCR
     */
    public $tesseract;

    /**
     * @var \App\Services\Requests\HttpRequest
     */
    public $httpRequest;

    /**
     * @var \App\Services\Model\SubjectService
     */
    private $subjectService;

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
     * @param \thiagoalessio\TesseractOCR\TesseractOCR $tesseract
     * @param \App\Services\Requests\HttpRequest $httpRequest
     * @param \App\Services\Model\SubjectService $subjectService
     */
    public function __construct(
        TesseractOCR $tesseract,
        HttpRequest $httpRequest,
        SubjectService $subjectService
    ) {
        $this->tesseract = $tesseract;
        $this->httpRequest = $httpRequest;
        $this->subjectService = $subjectService;
    }

    /**
     * Process ocr file.
     *
     * @param array $file
     * @param string $folderPath
     */
    public function process(array $file, string $folderPath)
    {
        $this->createImagePath($file, $folderPath);

        if ( ! $this->getImage($file)) {
            return;
        }

        $this->tesseractImage($file);

        Storage::delete($this->imgPath);

        return;
    }

    /**
     * Create paths.
     *
     * @param array $file
     * @param string $folderPath
     */
    private function createImagePath(array $file, string $folderPath)
    {
        $this->imgUrl = str_replace(' ', '%20', $file['url']);
        $this->imgPath = $folderPath.'/'.$file['subject_id'].'.jpg';
    }

    /**
     * Get image.
     *
     * @param array $file
     * @return bool
     */
    private function getImage(array $file)
    {
        try {
            if (Storage::exists($this->imgPath)) {
                return true;
            }

            $this->httpRequest->setHttpProvider();
            $this->httpRequest->getHttpClient()->request('GET', $this->imgUrl, ['sink' => Storage::path($this->imgPath)]);

            return true;
        } catch (GuzzleException $e) {
            $file['ocr'] = 'Error: ' . $e->getMessage();
            $this->updateSubject($file);

            return false;
        }
    }

    /**
     * Read image.
     *
     * @param array $file
     */
    private function tesseractImage(array $file)
    {
        try {
            $result = $this->tesseract->image(Storage::path($this->imgPath))->threadLimit(1)->run();
            $ocr = preg_replace('/\s+/', ' ', trim($result));
            $file['ocr'] = empty($ocr) ? 'Error: OCR returned empty string.' : $ocr;
            $this->updateSubject($file);
        } catch (Exception $e) {
            $file['ocr'] = $e->getMessage();
            $this->updateSubject($file);
        }
    }

    /**
     * Update subject in mongodb.
     *
     * @param array $file
     */
    private function updateSubject(array $file)
    {
        $subject = $this->subjectService->find($file['subject_id']);
        $subject->ocr = $file['ocr'];
        $subject->save();
    }
}