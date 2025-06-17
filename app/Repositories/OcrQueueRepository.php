<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories;

use App\Models\OcrQueue;

/**
 * Class OcrQueueRepository
 */
class OcrQueueRepository extends BaseRepository
{
    /**
     * OcrQueueRepository constructor.
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->model = $ocrQueue;
    }

    /**
     * Get ocr queue for poll command.
     */
    public function getOcrQueuesForPollCommand(): mixed
    {
        return $this->model->withCount([
            'files' => function ($q) {
                $q->where('processed', 1);
            },
        ])->with(['project.group', 'expedition'])->where('error', 0)->orderBy('id', 'asc')->get();
    }

    /**
     * Get ocr queue for process command.
     */
    public function getFirstQueue(bool $reset = false): ?OcrQueue
    {
        return $reset ?
            $this->model->orderBy('id')->first() :
            $this->model->where('error', 0)->where('status', 0)->orderBy('id', 'asc')->first();
    }
}
