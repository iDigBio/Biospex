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
namespace App\Listeners;

use App\Events\ImageExportEvent;
use App\Services\Process\SnsImageExportResultProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class ImageExportListener implements ShouldQueue
{
    /**
     * @var \App\Services\Process\SnsImageExportResultProcess
     */
    private SnsImageExportResultProcess $snsImageExportResultProcess;

    /**
     * Create the event listener.
     */
    public function __construct(SnsImageExportResultProcess $snsImageExportResultProcess)
    {
        $this->snsImageExportResultProcess = $snsImageExportResultProcess;
    }

    /**
     * Set tube for listener.
     *
     * @return string
     */
    public function viaQueue(): string
    {
        return config('config.queue.image_export_listener');
    }

    /**
     * Handle ImageExportEvent.
     * Updates the database appropriately for success or failure of each image processed.
     */
    public function handle(ImageExportEvent $event): void
    {
        $requestPayload = $event->payload['requestPayload'];
        $responsePayload = $event->payload['responsePayload'];

        // If errorMessage, something really went bad with the lambda function.
        if (isset($responsePayload['errorMessage'])) {
            $this->snsImageExportResultProcess->handleErrorMessage($requestPayload, $responsePayload['errorMessage']);
            return;
        }

        $this->snsImageExportResultProcess->handleResponse($responsePayload['statusCode'], $responsePayload['body']);
    }

    /**
     * Handle a job failure.
     * The error is handled differently than LabelReconciliationListener because it's updated in database for each image.
     */
    public function failed(ImageExportEvent $event, Throwable $exception): void
    {
        $this->snsImageExportResultProcess->handleErrorMessage($event->payload['requestPayload'], $exception->getMessage());
    }
}

/*
Payload example

{
  "version": "1.0",
  "timestamp": "2024-06-20T20:36:13.325Z",
  "requestContext": {
    "requestId": "a2de2a13-7440-4e89-bb55-eba853a8cbeb",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:imageProcessExportDev:$LATEST",
    "condition": "Success",
    "approximateInvokeCount": 1
  },
  "requestPayload": {
    "bucket": "biospex-dev",
    "queueId": 8,
    "subjectId": "65ce1fdde2e632f27807654d",
    "url": "https://sernecportal.org/imglib/seinet/sernec/FTU/FTU0016/FTU0016888.jpg",
    "dir": "scratch/8-2-46ccbb8b-4d56-4c11-91f4-f7754d1fc7a3",
    "env": null
  },
  "responseContext": {
    "statusCode": 200,
    "executedVersion": "$LATEST"
  },
  "responsePayload": {
    "statusCode": 200,
    "body": {
      "env": null,
      "bucket": "biospex-dev",
      "queueId": 8,
      "subjectId": "65ce1fdde2e632f27807654d",
      "message": ""
    }
  }
}

 */