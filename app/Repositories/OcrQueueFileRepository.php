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

use App\Models\OcrQueueFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OcrQueueRepository
 *
 * @package App\Repositories
 */
class OcrQueueFileRepository extends BaseRepository
{
    /**
     * OcrQueueRepository constructor.
     *
     * @param \App\Models\OcrQueueFile $ocrQueueFile
     */
    public function __construct(OcrQueueFile $ocrQueueFile)
    {

        $this->model = $ocrQueueFile;
    }

    /**
     * Get OcrQueueFile query.
     *
     * @param int $queueId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOcrQueueFileQuery(int $queueId): Builder
    {
        return $this->model->where('queue_id', $queueId);
    }

    /**
     * Get OcrQueueFile empty.
     *
     * @param int $queueId
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUnprocessedOcrQueueFiles(int $queueId): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->where('queue_id', $queueId)->where('processed', 0)->take(config('config.aws.lambda_ocr_count'))->get();
    }

    /**
     * Get OcrQueueFile with error.
     *
     * @param int $queueId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilesWithError(int $queueId): Collection
    {
        return $this->model->where('id', $queueId)->where('message', 'LIKE', '%Error%')->get();
    }
}