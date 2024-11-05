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

namespace App\Http\Controllers\Front;

use App\Events\ImageExportEvent;
use App\Events\LabelReconciliationEvent;
use App\Events\SnsTopicSubscriptionEvent;
use App\Events\TesseractOcrEvent;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AwsSnsController
{
    /**
     * Handle incoming SNS messages.
     * Checks the incoming message and fires the event for what's being requested.
     *
     * @see ImageExportEvent
     * @see LabelReconciliationEvent
     */
    public function __invoke(): \Illuminate\Http\Response
    {
        $message = Message::fromRawPostData();

        $validator = new MessageValidator(function ($certUrl) {
            return Cache::rememberForever($certUrl, function () use ($certUrl) {
                return Http::get($certUrl)->body();
            });
        });

        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            // Return 404 to pretend we are not here for SNS if invalid request
            return response('SNS Message Validation Error: '.$e->getMessage(), 404);
        }

        if (isset($message['Type']) && $message['Type'] === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            Http::get($message['SubscribeURL']);

            event(new SnsTopicSubscriptionEvent);

            return response('OK', 200);
        }

        if (isset($message['Type']) && $message['Type'] === 'Notification') {
            $payload = json_decode($message['Message'], true);

            $eventType = match (true) {
                str_contains($payload['requestContext']['functionArn'], config('config.aws.lambda_reconciliation_function')) => LabelReconciliationEvent::class,
                str_contains($payload['requestContext']['functionArn'], config('config.aws.lambda_ocr_function')) => TesseractOcrEvent::class,
                str_contains($payload['requestContext']['functionArn'], config('config.aws.lambda_export_function')) => ImageExportEvent::class,
                default => null,
            };

            if ($eventType === null) {
                return response(t('SNS Message Validation Error: Event Type Null'), 500);
            }

            event(new $eventType($payload));
        }

        return response('OK', 200);
    }
}
