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
     * Get all files without errors by queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithoutErrorByQueueId(string $queueId): Collection
    {
        return $this->model->with('subject')
            ->where('queue_id', $queueId)->where('error', 0)->get();
    }

    /**
     * Get files with errors by queue id.
     *
     * @param string $queueId
     * @return \Illuminate\Support\Collection
     */
    public function getFilesWithErrorsByQueueId(string $queueId): Collection
    {
        return $this->model->where('queue_id', $queueId)->where('error', 1)->get();
    }

    /**
     * Get all files by queue id.
     *
     * @param string $queueId
     * @param int $error
     * @return \Illuminate\Support\LazyCollection
     */
    public function getFilesByQueueId(string $queueId, int $error = 0): LazyCollection
    {
        return $this->model->where('queue_id', $queueId)->where('error', $error)->cursor();
    }
}