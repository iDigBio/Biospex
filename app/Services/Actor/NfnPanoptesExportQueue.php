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

namespace App\Services\Actor;

use App\Services\Model\ExportQueueService;
use App\Services\Model\ExportQueueFileService;
use App\Models\Actor;
use App\Services\MongoDbService;

/**
 * Class NfnPanoptesExportQueue
 *
 * @package App\Services\Actor
 */
class NfnPanoptesExportQueue
{
     /**
     * @var \App\Services\Model\ExportQueueService
     */
    private $exportQueueService;

    /**
     * @var \App\Services\Model\ExportQueueFileService
     */
    private $exportQueueFileService;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * NfnPanoptesExportQueue constructor.
     *
     * @param \App\Services\Model\ExportQueueService $exportQueueService
     * @param \App\Services\Model\ExportQueueFileService $exportQueueFileService
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        ExportQueueService $exportQueueService,
        ExportQueueFileService $exportQueueFileService,
        MongoDbService $mongoDbService
    ) {
        $this->exportQueueService = $exportQueueService;
        $this->exportQueueFileService = $exportQueueFileService;
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

        $queue = $this->exportQueueService->firstOrCreate($attributes);

        foreach ($subjects as $subject) {
            $attributes = [
                'queue_id' => $queue->id,
                'subject_id' => $subject['subject_id']
            ];
            $subject['queue_id'] = $queue->id;

            $this->exportQueueFileService->firstOrCreate($attributes, $subject);
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
