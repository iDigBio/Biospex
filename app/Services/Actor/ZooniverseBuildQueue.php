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

use App\Models\Actor;
use App\Models\ExportQueue;

/**
 * Class ZooniverseBuildQueue
 *
 * @package App\Services\Actor
 */
class ZooniverseBuildQueue implements ActorInterface
{
    /**
     * @var \App\Services\Actor\ZooniverseDbService
     */
    private $dbService;

    /**
     * ZooniverseBuildQueue constructor.
     *
     * @param \App\Services\Actor\ZooniverseDbService $dbService
     */
    public function __construct(
        ZooniverseDbService $dbService
    ) {
        $this->dbService = $dbService;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->createQueue($actor);

        try {
            $subjects = $this->dbService->subjectRepo->getAssignedByExpeditionId($actor->pivot->expedition_id);

            $subjects->each(function ($subject) use ($queue) {
                $this->createQueueFile($queue, $subject);
            });

        } catch (\Exception $e) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception(t('Error while trying to create queue files for export: %s', $e->getMessage() . ' ' . $e->getTraceAsString()));
        }
    }

    /**
     * Create queue for export.
     *
     * @param \App\Models\Actor $actor
     * @return \App\Models\ExportQueue
     */
    private function createQueue(Actor $actor): ExportQueue
    {
        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id'      => $actor->id,
        ];

        $queue = $this->dbService->exportQueueRepo->firstOrNew($attributes);
        $queue->queued = 1;
        $queue->error = 0;
        $queue->stage = 0;
        $queue->count = $actor->pivot->total;
        $queue->processed = 0;
        $queue->save();

        return $queue;
    }

    /**
     * Create queue file for subject.
     *
     * @param \App\Models\ExportQueue $queue
     * @param $subject
     */
    protected function createQueueFile(ExportQueue $queue, $subject): void
    {
        $attributes = [
            'queue_id'   => $queue->id,
            'subject_id' => (string) $subject->_id
        ];

        $file = $this->dbService->exportQueueFileRepo->firstOrNew($attributes);
        $file->url = $subject->accessURI;
        $file->error = 0;
        $file->error_message = null;
        $file->save();
    }
}