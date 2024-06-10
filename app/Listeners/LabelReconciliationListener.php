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
use Exception;
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
        return config('config.queues.sns_reconcile');
    }

    /**
     * Handle the event.
     * LabelReconciliationEvent has two results: Reconcile and Explained.
     *
     * @throws \Throwable
     */
    public function handle(LabelReconciliationEvent $event): void
    {
        $responsePayload = $event->payload['responsePayload'];

        // If errorMessage, something really went bad.
        if (isset($event->responsePayload['errorMessage'])) {
            throw new Exception($event->responsePayload['errorMessage']);
        }

        if ($responsePayload['statusCode'] !== 200) {
            throw new Exception('Invalid response status code: ' . $responsePayload['body']['message']);
        }

        $this->reconcileService->processEvent((int) $responsePayload['body']['expeditionId'], $responsePayload['body']['explanations']);
    }

    /**
     * Handle a job failure.
     *
     * @param LabelReconciliationEvent $event
     * @param Throwable $exception
     */
    public function failed(LabelReconciliationEvent $event, Throwable $exception)
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
{
  "version": "1.0",
  "timestamp": "2024-06-05T16:27:43.489Z",
  "requestContext": {
    "requestId": "bdc9d39a-06f6-4499-8a27-d40ac7ab18b2",
    "functionArn": "arn:aws:lambda:us-east-2:147899039648:function:labelReconciliations:$LATEST",
    "condition": "Success",
    "approximateInvokeCount": 1
  },
  "requestPayload": {
    "bucket": "biospex-dev",
    "key": "zooniverse\/classification\/999999.csv",
    "explanations": true
  },
  "responseContext": {
    "statusCode": 200,
    "executedVersion": "$LATEST"
  },
  "responsePayload": {
    "statusCode": 200,
    "body": {
      "bucket": "biospex-dev",
      "expeditionId": "999999"
    }
  }
}
*/