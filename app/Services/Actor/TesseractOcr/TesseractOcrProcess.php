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

namespace App\Services\Actor\TesseractOcr;

use App\Models\OcrQueue;
use App\Models\OcrQueueFile;
use App\Notifications\Traits\ButtonTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OcrService
 */
readonly class TesseractOcrProcess
{
    use ButtonTrait;

    /**
     * Ocr constructor.
     */
    public function __construct(
        private OcrQueue $ocrQueue,
        private OcrQueueFile $ocrQueueFile,
    ) {}

    /**
     * Return ocr queue for command process.
     */
    public function getFirstQueue(bool $reset = false): Model|Builder|null
    {
        return $reset ?
            $this->ocrQueue->orderBy('id', 'asc')->first() :
            $this->ocrQueue->where('error', 0)->orderBy('id', 'asc')->first();
    }

    /**
     * Get unprocessed ocr queue files.
     * Limited return depending on config.
     */
    public function getUnprocessedOcrQueueFiles(int $queueId, int $take = 50): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->ocrQueueFile->where('queue_id', $queueId)->where('processed', 0)->take($take)->get();
    }
}
