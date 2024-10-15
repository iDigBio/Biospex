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
use App\Models\ExportQueueFile;
use App\Services\Subject\SubjectService;

/**
 * Class ZooniverseBuildQueue
 */
class ZooniverseBuildQueue
{
    /**
     * Construct.
     */
    public function __construct(
        private ExportQueue $exportQueue,
        private ExportQueueFile $exportQueueFile,
        private SubjectService $subjectService
    ) {}

    /**
     * Process actor.
     *
     * @throws \Exception
     */
    public function process(Actor $actor): void
    {
        $attributes = [
            'expedition_id' => $actor->pivot->expedition_id,
            'actor_id' => $actor->id,
        ];

        $queue = $this->exportQueue->firstOrNew($attributes);
        $queue->queued = 0;
        $queue->error = 0;
        $queue->stage = 0;
        $queue->total = $actor->pivot->total;
        $queue->save();

        $this->buildFiles($queue);
    }

    /**
     * Build queue files table.
     */
    public function buildFiles(ExportQueue $exportQueue): void
    {
        $subjects = $this->subjectService->getSubjectCursorForExport($exportQueue->expedition_id);

        $subjects->each(function ($subject) use ($exportQueue) {
            $attributes = [
                'queue_id' => $exportQueue->id,
                'subject_id' => (string) $subject->_id,
            ];

            $file = $this->exportQueueFile->firstOrNew($attributes);
            $file->access_uri = $subject->accessURI;
            $file->processed = 0;
            $file->message = null;
            $file->save();
        });
    }
}
