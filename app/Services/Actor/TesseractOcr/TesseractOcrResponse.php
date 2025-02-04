<?php

/*
 * Copyright (c) 2022. Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Actor\TesseractOcr;

use App\Models\OcrQueueFile;

class TesseractOcrResponse
{
    /**
     * Create a new instance.
     * TODO: DI for storage facade
     */
    public function __construct(protected OcrQueueFile $ocrQueueFile) {}

    /**
     * Process ocr payload.
     *
     * @see \App\Listeners\TesseractOcrListener
     */
    public function process(array $data): void
    {
        $requestPayload = $data['requestPayload'];
        $responsePayload = $data['responsePayload'];
        $statusCode = (int) $responsePayload['statusCode'];

        // If errorMessage, something really went bad with lambda function.
        isset($responsePayload['errorMessage']) || $statusCode === 500 ?
            $this->handleErrorMessage($requestPayload, $responsePayload, true) :
            $this->handleResponse($responsePayload['body']);
    }

    /**
     * Handle error message.
     * $requestPayload['id'] is the ocr_queue_files id.
     */
    public function handleErrorMessage(array $requestPayload, array $responsePayload, bool $statusCode = false): void
    {
        if ($statusCode) {
            $message = 'Error: Unable to complete OCR. 500 error from lambda function. ';
            $message .= $responsePayload['body']['message']['message'];
        } else {
            $message = empty($responsePayload['errorMessage']) ?
                'Error: Unable to complete OCR.' :
                'Error: '.$responsePayload['errorMessage'];
        }

        \Storage::disk('s3')->put($requestPayload['key'], $message);

        $file = $this->ocrQueueFile->find($requestPayload['file']);
        $file->processed = 1;
        $file->save();
    }

    /**
     * Handle response for success or failure.
     * $body['id'] is the ocr_queue_files id.
     */
    public function handleResponse(array $body): void
    {
        $attributes = [
            'processed' => 1,
        ];
        $ocrQueueFile = $this->ocrQueueFile->find($body['file']);
        $ocrQueueFile->fill($attributes)->save();
    }
}
