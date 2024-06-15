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

use App\Models\Subject;
use App\Services\Requests\HttpRequest;
use GuzzleHttp\Exception\GuzzleException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Storage;
use Exception;

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
     * @var Subject
     */
    private $subject;

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
     */
    public function __construct(
        TesseractOCR $tesseract,
        HttpRequest $httpRequest
    ) {
        $this->tesseract = $tesseract;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Process ocr file.
     *
     * @param Subject $subject
     */
    public function process(Subject $subject): void
    {
        $this->subject = $subject;

        $file['subject_id'] = (string) $subject->_id;
        $file['ocr'] = $subject->ocr;
        $file['url'] = $subject->accessURI;

        $this->createImagePath($file);

        if (! $this->getImage($file)) {
            \File::delete($this->imgPath);

            return;
        }

        $this->tesseractImage($file);

        \File::delete($this->imgPath);
    }

    /**
     * Create paths.
     *
     * @param array $file
     */
    private function createImagePath(array $file): void
    {
        $this->imgUrl = str_replace(' ', '%20', $file['url']);
        $this->imgPath = Storage::disk('efs')->path(config('config.ocr_dir').'/'.$file['subject_id'].'.jpg');
    }

    /**
     * Get image.
     *
     * @param array $file
     * @return bool
     */
    private function getImage(array $file): bool
    {
        try {
            if (\File::exists($this->imgPath)) {
                return true;
            }

            $this->httpRequest->setHttpProvider();
            $this->httpRequest->getHttpClient()->request('GET', $this->imgUrl, ['sink' => $this->imgPath]);

            return true;
        } catch (GuzzleException $e) {
            $file['ocr'] = 'Error: '.$e->getMessage();
            $this->updateSubject($file);

            return false;
        }
    }

    /**
     * Read image.
     *
     * @param array $file
     */
    private function tesseractImage(array $file): void
    {
        try {
            $result = $this->tesseract->image($this->imgPath)->threadLimit(1)->run();
            $ocr = preg_replace('/\s+/', ' ', trim($result));
            $file['ocr'] = empty($ocr) ? 'Error: OCR returned empty string.' : trim($ocr);
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
    private function updateSubject(array $file): void
    {
        $this->subject->ocr = $file['ocr'];
        $this->subject->save();
    }
}