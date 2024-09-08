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

namespace App\Services\Actor\Zooniverse;

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Repositories\SubjectRepository;

/**
 * Class ZooniverseBuildQueue
 */
class ZooniverseBuildQueue
{
    private ExportQueueRepository $exportQueueRepository;

    private ExportQueueFileRepository $exportQueueFileRepository;

    private SubjectRepository $subjectRepository;

    /**
     * Construct.
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository,
        SubjectRepository $subjectRepository
    ) {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * Process actor.
     *
     * @throws \Exception
     */
    public function process(Actor $actor): void
    {
        $queue = $this->exportQueueRepository->createQueue($actor->pivot->expedition_id, $actor->id, $actor->pivot->total);
        $this->buildFiles($queue);
    }

    /**
     * Build queue files table.
     */
    public function buildFiles(ExportQueue $exportQueue): void
    {
        $subjects = $this->subjectRepository->getSubjectCursorForExport($exportQueue->expedition_id);

        $subjects->each(function ($subject) use ($exportQueue) {
            $this->exportQueueFileRepository->createQueueFile($exportQueue, $subject);
        });
    }
}
