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
        return config('config.queues.default');
    }

    /**
     * Handle the event.
     */
    public function handle(ImageExportEvent $event): void
    {
        $this->snsImageExportResultProcess->process($event->payload);
    }


    public function failed(ImageExportEvent $event, Throwable $exception)
    {
        \Log::error('ImageExportListener failed', ['event' => $event, 'exception' => $exception]);
    }
}

/*
Payload example

{
  "version": "1.0",
  "timestamp": "2022-08-21T17:59:27.529Z",
  "requestContext": {
    "requestId": "36a960b2-bd75-4cee-b5b1-ffd567ebd94d",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:imageProcessExport:$LATEST", arn:aws:lambda:us-east-2:147899039648:function:tesseractOcr:$LATEST
    "condition": "Success",
    "approximateInvokeCount": 1
  },
  "requestPayload": {
    "queueId": 10,
    "subjectId": "6298bb95c5143f1cc750d5a4",
    "url": "http:\/\/cdn.flmnh.ufl.edu\/Herbarium\/jpg\/185\/185753a1.jpg",
    "dir": "scratch\/testing-scratch"
  },
  "responseContext": {
    "statusCode": 200,
    "executedVersion": "$LATEST"
  },
  "responsePayload": {
    "statusCode": 200,
    "body": "{\"queueId\":10,\"subjectId\":\"6298bb95c5143f1cc750d5a4\",\"message\":\"\"}"
  }
}
 */