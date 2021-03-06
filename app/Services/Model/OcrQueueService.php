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

use App\Models\OcrQueue;

/**
 * Class OcrQueueService
 *
 * @package App\Services\Model
 */
class OcrQueueService extends BaseModelService
{
    /**
     * OcrQueueService constructor.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {

        $this->model = $ocrQueue;
    }

    /**
     * Get ocr queue for poll command.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOcrQueuesForPollCommand()
    {
        return $this->model->with(['project.group', 'expedition'])
            ->where('error', 0)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get ocr queue for process command.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOcrQueueForOcrProcessCommand()
    {
        return $this->model->with('project.group.owner')
            ->where('error', 0)
            ->orderBy('id', 'asc')
            ->first();
    }
}