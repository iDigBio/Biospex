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
                t('LabelReconciliationListener failed for Expedition ID: %s', $event->payload['responsePayload']['body']['expeditionId']),
                t('Action: %s', $event->payload['responsePayload']['body']['explanations'] ? 'Explained' : 'Reconciled'),
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