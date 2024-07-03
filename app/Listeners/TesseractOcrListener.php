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
namespace App\Listeners;

use App\Events\TesseractOcrEvent;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class TesseractOcrListener implements ShouldQueue
{
    /**
     * @var \App\Services\Actor\TesseractOcr\TesseractOcrResponse $tesseractOcrResponse
     */
    private TesseractOcrResponse $tesseractOcrResponse;

    /**
     * Create the event listener.
     */
    public function __construct(TesseractOcrResponse $tesseractOcrResponse)
    {
        $this->tesseractOcrResponse = $tesseractOcrResponse;
    }

    /**
     * Handle the event.
     *
     * @throws \Exception
     */
    public function handle(TesseractOcrEvent $event): void
    {
        $this->tesseractOcrResponse->process($event->payload);
    }

    /**
     * Set tube for listener.
     *
     * @return string
     */
    public function viaQueue(): string
    {
        return config('config.queue.tesseract_ocr_listener');
    }

    /**
     * Handle a LabelReconciliationEvent failure.
     * Will send email to admin so they can investigate.
     *
     * @param TesseractOcrEvent $event
     * @param Throwable $exception
     */
    public function failed(TesseractOcrEvent $event, Throwable $exception): void
    {
        $attributes = [
            'subject' => t('TesseractOcrListener Failed'),
            'html'    => [
                t('TesseractOcrListener failed for Queue File ID: %s', $event->payload['responsePayload']['body']['file']),
                t('Error: %s', $exception->getMessage()),
                t('File: %s', $exception->getFile()),
                t('Line: %s', $exception->getLine()),
            ],
        ];

        $user = User::find(1);
        $user->notify(new Generic($attributes, true));
    }
}

/*
{
  "version": "1.0",
  "timestamp": "2024-06-21T00:12:50.144Z",
  "requestContext": {
    "requestId": "841be8dc-7a41-49aa-8dce-65d7d7e0ec54",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:tesseractOcr:$LATEST",
    "condition": "Success",
    "approximateInvokeCount": 1
  },
  "requestPayload": {
    "bucket": "biospex-dev",
    "key": "zooniverse/lambda-ocr/615da36c65b16554e4781ed9.txt",
    "file": 5,
    "uri": "https://cdn.floridamuseum.ufl.edu/herbarium/jpg/092/92321s1.jpg"
  },
  "responseContext": {
    "statusCode": 200,
    "executedVersion": "$LATEST"
  },
  "responsePayload": {
    "statusCode": 200,
    "body": {
      "bucket": "biospex-dev",
      "key": "zooniverse/lambda-ocr/615da36c65b16554e4781ed9.txt",
      "file": 5
      "message": "Ocr text here"
    }
  }
}


 */