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

namespace App\Services\Model;

use App\Models\ExportQueueFile;
use App\Services\Model\Traits\ModelTrait;
use Illuminate\Support\Collection;

/**
 * Class ExportQueueFileService
 *
 * @package App\Services\Model
 */
class ExportQueueFileService
{
    use ModelTrait;

    /**
     * @var \App\Models\ExportQueueFile
     */
    private $model;

    /**
     * ExportQueueFileService constructor.
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
     * @return \Illuminate\Support\Collection
     */
    public function getFilesByQueueId(string $queueId): Collection
    {
        return $this->model->where('queue_id', $queueId)->where('error', 0)->get();
    }
}