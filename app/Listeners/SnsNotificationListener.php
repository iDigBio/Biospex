<?php

namespace App\Listeners;

use App\Services\Process\SnsImageExportResultProcess;
use Illuminate\Contracts\Queue\ShouldQueue;

class SnsNotificationListener implements ShouldQueue
{
    /**
     * @var \App\Services\Process\SnsImageExportResultProcess
     */
    private SnsImageExportResultProcess $snsImageExportResultProcess;

    /**
     * Create the event listener.
     *
     * @param \App\Services\Process\SnsImageExportResultProcess $snsImageExportResultProcess
     */
    public function __construct(SnsImageExportResultProcess $snsImageExportResultProcess)
    {
        $this->snsImageExportResultProcess = $snsImageExportResultProcess;
    }

    /**
     * Set tube for listener.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function viaQueue()
    {
        return config('config.queue.sns_image');
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        // $event->payload is the data passed to the event.
        $content = json_decode($event->payload['message']['Message'], true);
        $this->snsImageExportResultProcess->process($content);
    }
}

/*
{
  "version": "1.0",
  "timestamp": "2022-08-21T17:59:27.529Z",
  "requestContext": {
    "requestId": "36a960b2-bd75-4cee-b5b1-ffd567ebd94d",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:imageProcessExport:$LATEST",
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