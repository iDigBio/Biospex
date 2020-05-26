<?php
/**
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

namespace App\Services\Actor;

use App\Repositories\Interfaces\ExportQueue;
use App\Repositories\Interfaces\ExportQueueFile;
use App\Models\Actor;
use App\Services\MongoDbService;

class NfnPanoptesExportQueue
{
     /**
     * @var \App\Repositories\Interfaces\ExportQueue
     */
    private $exportQueueContract;

    /**
     * @var \App\Repositories\Interfaces\ExportQueueFile
     */
    private $exportQueueFileContract;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * NfnPanoptesExportQueue constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueue $exportQueueContract
     * @param \App\Repositories\Interfaces\ExportQueueFile $exportQueueFileContract
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        ExportQueue $exportQueueContract,
        ExportQueueFile $exportQueueFileContract,
        MongoDbService $mongoDbService
    ) {
        $this->exportQueueContract = $exportQueueContract;
        $this->exportQueueFileContract = $exportQueueFileContract;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Queue jobs for exports.
     * fa-refresh
     *
     * @param Actor $actor
     * @see NfnPanoptes::actor() To set actor for this method.
     * @see ExportQueueEventSubscriber::created() Event fired when queues saved.
     */
    public function createQueue(Actor $actor)
    {
        $subjects = $this->buildSubjectsArray($actor->pivot->expedition_id);

        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id'      => $actor->id,
            'count'         => count($subjects),
        ];

        $queue = $this->exportQueueContract->firstOrCreate($attributes);

        foreach ($subjects as $subject) {
            $attributes = [
                'queue_id' => $queue->id,
                'subject_id' => $subject['subject_id']
            ];
            $subject['queue_id'] = $queue->id;

            $this->exportQueueFileContract->firstOrCreate($attributes, $subject);
        }

        event('exportQueue.updated');
    }

    /**
     * Build the subject array in expedition being processed.
     *
     * @param string $expeditionId
     * @return array
     */
    protected function buildSubjectsArray(string $expeditionId): array
    {
        $query = ['expedition_ids' => (int) $expeditionId];

        $this->mongoDbService->setCollection('subjects');
        $results = $this->mongoDbService->find($query);

        $fileData = [];
        foreach ($results as $doc) {
            $fileData[] = [
                'subject_id' => (string) $doc['_id'],
                'url'        => $doc['accessURI'],
            ];
        }

        return $fileData;
    }
}
