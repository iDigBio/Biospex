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

use App\Repositories\OcrQueueFileRepository;

class TesseractOcrResponse
{
    private OcrQueueFileRepository $ocrQueueFileRepo;

    /**
     * Create a new instance.
     */
    public function __construct(OcrQueueFileRepository $ocrQueueFileRepo)
    {
        $this->ocrQueueFileRepo = $ocrQueueFileRepo;
    }

    /**
     * Process ocr payload.
     *
     * @see \App\Listeners\TesseractOcrListener
     */
    public function process(array $payload): void
    {
        $requestPayload = $payload['requestPayload'];
        $responsePayload = $payload['responsePayload'];

        // If errorMessage, something really went bad with lambda function.
        isset($responsePayload['errorMessage']) ?
            $this->handleErrorMessage($requestPayload, $responsePayload['errorMessage']) :
            $this->handleResponse($responsePayload['body']);
    }

    /**
     * Handle error message.
     * $requestPayload['id'] is the ocr_queue_files id.
     */
    public function handleErrorMessage(array $requestPayload, string $errorMessage): void
    {
        $message = empty($errorMessage) ? 'Error: Unable to complete OCR.' : 'Error: '.$errorMessage;
        \Storage::disk('s3')->put($requestPayload['key'], $message);

        $file = $this->ocrQueueFileRepo->find($requestPayload['file']);
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
        $this->ocrQueueFileRepo->update($attributes, $body['file']);
    }
}
