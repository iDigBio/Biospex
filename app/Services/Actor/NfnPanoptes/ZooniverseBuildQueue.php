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

namespace App\Services\Actor\NfnPanoptes;

use App\Models\Actor;
use App\Services\Actor\ActorInterface;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Repositories\SubjectRepository;

/**
 * Class ZooniverseBuildQueue
 *
 * @package App\Services\Actor
 */
class ZooniverseBuildQueue implements ActorInterface
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
     * @var \App\Repositories\SubjectRepository
     */
    private SubjectRepository $subjectRepository;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Repositories\SubjectRepository $subjectRepository
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository,
        SubjectRepository $subjectRepository
    )
    {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\Actor $actor
     * @return void
     * @throws \Exception
     */
    public function process(Actor $actor)
    {
        $queue = $this->exportQueueRepository->createQueue($actor);

        try {
            $subjects = $this->subjectRepository->getAssignedByExpeditionId($actor->pivot->expedition_id);

            $subjects->each(function ($subject) use ($queue) {
                $this->exportQueueFileRepository->createQueueFile($queue, $subject);
            });

        } catch (\Exception $e) {
            $queue->error = 1;
            $queue->queued = 0;
            $queue->processed = 0;
            $queue->save();

            throw new \Exception(t('Error while trying to create queue files for export: %s', $e->getMessage() . ' ' . $e->getTraceAsString()));
        }
    }
}