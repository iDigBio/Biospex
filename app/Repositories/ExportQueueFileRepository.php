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
     * Return model.
     *
     * @return \App\Models\ExportQueueFile|\Illuminate\Database\Eloquent\Model|\MongoDB\Laravel\Eloquent\Model
     */
    public function model(): ExportQueueFile|\Illuminate\Database\Eloquent\Model|\MongoDB\Laravel\Eloquent\Model
    {
        return $this->model;
    }

    /**
     * Get uncompleted export queue file count.
     *
     * @param int $queueId
     * @return int
     */
    public function getUncompletedCount(int $queueId): int
    {
        return $this->model->where('queue_id', $queueId)->inComplete()->count();
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
        $file->url = $subject->accessURI;
        $file->completed = 0;
        $file->error_message = null;
        $file->save();
    }

    /**
     * Get queue files with errors for reporting.
     *
     * @param int $queueId
     * @return array
     */
    public function getQueueFileErrorsData(int $queueId): array
    {
        $data = [];
        $remove = array_flip(['id', 'queue_id', 'completed', 'created_at', 'updated_at']);

        $callback = function ($files) use($remove, &$data) {
            $data = $files->map(function ($file) use ($remove) {
                return array_diff_key($file->toArray(), $remove);
            })->toArray();
        };

        $this->model->where('queue_id', $queueId)
            ->whereNotNull('error_message')
            ->chunk(100, $callback);

        return $data;
    }

    /**
     * Get count.
     *
     * @param int $queueId
     * @return int
     */
    public function getExportFilesCount(int $queueId): int
    {
        return $this->model->where('queue_id', $queueId)->count();
    }
}