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

namespace App\Services\Process;

use App\Models\ExportQueueFile;

/**
 * Class SnsImageExportResultProcess
 */
class SnsImageExportResultProcess
{
    /**
     * Construct.
     */
    public function __construct(protected ExportQueueFile $exportQueueFile) {}

    /**
     * Handle hard failure of lambda function.
     * Do not update queue if hard error.
     */
    public function handleErrorMessage(array $requestPayload, string $errorMessage): void
    {
        $this->updateQueueFile($requestPayload['subjectId'], $errorMessage);
    }

    /**
     * Handle response for success or failure.
     */
    public function handleResponse(int $statusCode, array $body): void
    {
        $this->updateQueueFile($body['subjectId'], $body['message']);
    }

    /**
     * Update queue file with result.
     * Response status can be 200 or 500. If 200, message is blank. If 500, there is an error message.
     */
    private function updateQueueFile(string $subjectId, ?string $message = null): void
    {
        $attributes = [
            'subject_id' => $subjectId,
            'processed' => 1,
            'message' => $message,
        ];

        $exportQueueFile = $this->exportQueueFile->where('subject_id', $subjectId)->first();
        $exportQueueFile->fill($attributes)->save();
    }
}

/*
[2022-08-16 16:28:37] dev.ALERT: Array
(
    [version] => 1.0
    [timestamp] => 2022-08-16T16:28:37.532Z
    [requestContext] => Array
        (
            [requestId] => 1a7d4e81-6329-4ae1-9fa9-f2093fe0a2eb
            [functionArn] => arn:aws:lambda:us-east-2:147899039648:function:imageProcessExport:$LATEST
            [condition] => Success
            [approximateInvokeCount] => 2
        )

    [requestPayload] => Array
        (
            [queueId] => 2
            [subjectId] => 6298bb95c5143f1cc750d5a4
            [url] => http://cdn.flmnh.ufl.edu/Herbarium/jpg/185/185753a1.jpg
            [dir] => scratch/testing-scratch
        )

    [responseContext] => Array
        (
            [statusCode] => 200
            [executedVersion] => $LATEST
        )

    [responsePayload] => Array
        (
            [statusCode] => 500
            [body] => {"subjectId":"6298bb95c5143f1cc750d5a4","message":{"message":"Missing required key 'Bucket' in params","code":"MissingRequiredParameter","time":"2022-08-16T16:28:37.293Z"}}
        )

)

 */
