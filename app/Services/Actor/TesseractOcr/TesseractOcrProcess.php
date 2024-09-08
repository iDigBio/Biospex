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

use App\Notifications\Traits\ButtonTrait;
use App\Repositories\OcrQueueFileRepository;
use App\Repositories\OcrQueueRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OcrService
 */
class TesseractOcrProcess
{
    use ButtonTrait;

    private OcrQueueRepository $ocrQueueRepo;

    private OcrQueueFileRepository $ocrQueueFileRepo;

    /**
     * Ocr constructor.
     */
    public function __construct(
        OcrQueueRepository $ocrQueueRepo,
        OcrQueueFileRepository $ocrQueueFileRepo,
    ) {
        $this->ocrQueueRepo = $ocrQueueRepo;
        $this->ocrQueueFileRepo = $ocrQueueFileRepo;
    }

    /**
     * Return ocr queue for command process.
     */
    public function getFirstQueue(bool $reset = false): Model|Builder|null
    {
        return $this->ocrQueueRepo->getFirstQueue($reset);
    }

    /**
     * Get unprocessed ocr queue files.
     * Limited return depending on config.
     */
    public function getUnprocessedOcrQueueFiles(int $queueId, int $take = 50): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->ocrQueueFileRepo->getUnprocessedOcrQueueFiles($queueId, $take);
    }
}
