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

use App\Events\LabelReconciliationEvent;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Reconcile\ReconcileService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

/**
 * Class LabelReconciliationListener
 *
 * Listener for LabelReconciliationEvent.
 * Handles the lambda reconcile and explained process.
 */
class LabelReconciliationListener implements ShouldQueue
{
    /**
     * @var \App\Services\Reconcile\ReconcileService
     */
    public ReconcileService $reconcileService;

    /**
     * Create the event listener.
     */
    public function __construct(ReconcileService $reconcileService)
    {
        $this->reconcileService = $reconcileService;
    }

    /**
     * Set tube for listener.
     *
     * @return string
     */
    public function viaQueue(): string
    {
        return config('config.queue.reconciliation_listener');
    }

    /**
     * Handle the event.
     * LabelReconciliationEvent has two results: Reconcile and Explained.
     *
     * @throws \Throwable
     */
    public function handle(LabelReconciliationEvent $event): void
    {
        $this->reconcileService->process($event->payload);
    }

    /**
     * Handle a LabelReconciliationEvent failure.
     * Will send email to admin so they can investigate.
     *
     * @param LabelReconciliationEvent $event
     * @param Throwable $exception
     */
    public function failed(LabelReconciliationEvent $event, Throwable $exception): void
    {
        $attributes = [
            'subject' => t('LabelReconciliationListener Failed'),
            'html'    => [
                t('LabelReconciliationListener failed'),
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
 Payload example:
Message:
{
  "version": "1.0",
  "timestamp": "2024-06-20T23:01:06.677Z",
  "requestContext": {
    "requestId": "e3b42956-4a20-4222-9e95-74be136528d6",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:labelReconciliations:$LATEST",
    "condition": "Success",
    "approximateInvokeCount": 1
  },
  "requestPayload": {
    "Records": [
      {
        "eventVersion": "2.1",
        "eventSource": "aws:s3",
        "awsRegion": "us-east-2",
        "eventTime": "2024-06-20T23:00:10.335Z",
        "eventName": "ObjectCreated:Copy",
        "userIdentity": {
          "principalId": "AWS:AIDASE33YH6QGEDWHRUJ4"
        },
        "requestParameters": {
          "sourceIPAddress": "69.244.218.162"
        },
        "responseElements": {
          "x-amz-request-id": "WP78D359MFNEFT3J",
          "x-amz-id-2": "WIbmuYqNd5ibjbEfdvPCgM625au8ZXJEBqMU3J/zsg8rg+0+CeiSINcyFRXgcZTFdKs+qjtybuL5o2QR5xAuDqdBEBwV69Ez"
        },
        "s3": {
          "s3SchemaVersion": "1.0",
          "configurationId": "70f061ff-a313-449d-be47-d9fab9b982ee",
          "bucket": {
            "name": "biospex-dev",
            "ownerIdentity": {
              "principalId": "A3MAU6JAG3CLTW"
            },
            "arn": "arn:aws:s3:::biospex-dev"
          },
          "object": {
            "key": "zooniverse/lambda-reconciliation/999999.csv",
            "size": 19241604,
            "eTag": "242fc2ed974431e978dad6d64ef55089",
            "versionId": "OIaeqfOyRo6ByNjN0tc.Xb_pgPGHSLiJ",
            "sequencer": "006674B479D12B157C"
          }
        }
      }
    ]
  },
  "responseContext": {
    "statusCode": 200,
    "executedVersion": "$LATEST"
  },
  "responsePayload": {
    "statusCode": 200,
    "body": {
      "bucket": "biospex-dev",
      "expeditionId": "999999",
      "explanations": false
    }
  }
}

*/