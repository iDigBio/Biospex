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

namespace App\Repositories;

use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * Class ExportQueueFileRepository
 *
 * @package App\Repositories
 */
class ExportQueueFileRepository extends BaseRepository
{
    /**
     * ExportQueueFileRepository constructor.
     *
     * @param \App\Models\ExportQueueFile $exportQueueFile
     */
    public function __construct(ExportQueueFile $exportQueueFile)
    {
        $this->model = $exportQueueFile;
    }

    /**
     * Model for repository.
     *
     * @return \App\Models\ExportQueueFile
     */
    public function model(): ExportQueueFile
    {
        return $this->model;
    }

    /**
     * Create queue file for subject.
     *
     * @param \App\Models\ExportQueue $queue
     * @param $subject
     */
    public function createQueueFile(ExportQueue $queue, $subject): void
    {
        $attributes = [
            'queue_id'   => $queue->id,
            'subject_id' => (string) $subject->_id
        ];

        $file = $this->model->firstOrNew($attributes);
        $file->access_uri = $subject->accessURI;
        $file->processed = 0;
        $file->message = null;
        $file->save();
    }

    /**
     * Get uncompleted export queue file count.
     *
     * @param int $queueId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnprocessedExportQueueFiles(int $queueId, int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('queue_id', $queueId)
            ->where('processed', 0)
            ->orderBy('id')
            ->take($limit)->get();
    }

    public function getExportQueueFileCursor(int $queueId): LazyCollection
    {
        return $this->model->where('queue_id', $queueId)->cursor();
    }

    /**
     * Get queue files with errors for reporting.
     *
     * @param int $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getExportQueueFileWithErrors(int $queueId): Collection
    {
        return $this->model->where('queue_id', $queueId)
            ->whereNotNull('message')
            ->get(['subject_id', 'message']);
    }
}