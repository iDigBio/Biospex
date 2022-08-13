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
 * Class SqsImageExportProcess
 */
class SqsImageExportProcess
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
     * Process result from Lambda function.
     *
     * @param array $data
     * @return void
     */
    public function process(array $data)
    {
        $response = $data['responsePayload'];
        $status = $response['statusCode'];
        $body = json_decode($response['body'], true);

        $this->updateQueue($body['queueId']);

        $this->updateQueueFile($status, $body);

        \Artisan::call('export:poll');
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
     * @param int $status
     * @param array $body
     * @return void
     */
    private function updateQueueFile(int $status, array $body): void
    {
        $data = [
            'subject_id'    => $body['subjectId'],
            'completed'     => 1,
            'error_message' => $status === 200 ? null : $body['message']
        ];
        $this->exportQueueFileRepository->updateBy($data, 'subject_id', $body['subjectId']);
    }
}