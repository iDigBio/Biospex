<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

namespace App\Services\Reconcile;

use App\Traits\SkipZooniverse;
use Artisan;
use Aws\Sqs\SqsClient;

/**
 * Class ReconcileService
 *
 * Handles all methods for the reconcile and explained process.
 *
 * @see \App\Listeners\LabelReconciliationListener
 */
class ReconcileService
{
    use SkipZooniverse;

    /**
     * ReconcileService constructor.
     */
    public function __construct(protected SqsClient $sqs) {}

    /**
     * Send message to the correct environment-specific trigger queue
     */
    public function sendToReconcileTriggerQueue(int $expeditionId, bool $explanations = false): void
    {
        $name = config('services.aws.queues.reconcile_trigger');

        if (! $name) {
            throw new \RuntimeException('Missing config: services.aws.reconcile_trigger');
        }

        $queueUrl = $this->sqs->getQueueUrl(['QueueName' => $name])['QueueUrl'];

        $message = [
            'expeditionId' => (string) $expeditionId,
            'bucket' => config('filesystems.disks.s3.bucket'),
            'explanations' => $explanations,
        ];

        $this->sqs->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => json_encode($message),
        ]);

        Artisan::call('reconcile:listen-controller start');
    }
}
