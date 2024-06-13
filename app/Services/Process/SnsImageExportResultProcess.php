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

use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;

/**
 * Class SnsImageExportResultProcess
 */
class SnsImageExportResultProcess
{
    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository
    )
    {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
    }

    /**
     * Handle hard failure of lambda function.
     *
     * @param array $requestPayload
     * @param string $errorMessage
     * @return void
     */
    public function handleErrorMessage(array $requestPayload, string $errorMessage): void
    {
        $queueId = $requestPayload['queueId'];
        $subjectId = $requestPayload['subjectId'];
        $message = $errorMessage;

        $this->updateQueueFile($subjectId, $message);

        $this->updateQueue($queueId);
    }

    /**
     * Handle response for success or failuer.
     *
     * @param array $responsePayload
     * @return void
     */
    public function handleResponse(array $responsePayload): void
    {
        $statusCode = $responsePayload['statusCode'];
        $body = json_decode($responsePayload['body'], true);
        $message = $statusCode === 200 ? null :
            (is_array($body['message']) ? json_encode($body['message']) : $body['message']);

        $this->updateQueueFile($body['subjectId'], $message);

        $this->updateQueue($body['queueId']);
    }

    /**
     * Update queue processed number.
     *
     * @param string $queueId
     * @return void
     */
    private function updateQueue(string $queueId)
    {
        $queue = $this->exportQueueRepository->find($queueId);
        $queue->processed = $queue->processed + 1;
        $queue->save();
    }

    /**
     * Update queue file with result.
     *
     * @param string $subjectId
     * @param string|null $message
     * @return void
     */
    private function updateQueueFile(string $subjectId, string $message = null): void
    {
        $attributes = [
            'subject_id'    => $subjectId,
            'completed'     => 1,
            'error_message' => $message
        ];
        $this->exportQueueFileRepository->updateBy($attributes, 'subject_id', $subjectId);
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