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
use App\Notifications\Traits\ButtonTrait;
use App\Repositories\OcrQueueFileRepository;
use App\Repositories\OcrQueueRepository;
use App\Repositories\SubjectRepository;

/**
 * Class OcrService
 *
 * @package App\Services\Process
 */
class TesseractOcrBuild
{
    use ButtonTrait;

    /**
     * @var \App\Repositories\OcrQueueRepository
     */
    private OcrQueueRepository $ocrQueueRepo;

    /**
     * @var \App\Repositories\OcrQueueFileRepository
     */
    private OcrQueueFileRepository $ocrQueueFileRepo;

    /**
     * @var \App\Repositories\SubjectRepository
     */
    private SubjectRepository $subjectRepo;

    /**
     * Ocr constructor.
     *
     * @param \App\Repositories\OcrQueueRepository $ocrQueueRepo
     * @param \App\Repositories\OcrQueueFileRepository $ocrQueueFileRepo
     * @param \App\Repositories\SubjectRepository $subjectRepo
     */
    public function __construct(
        OcrQueueRepository $ocrQueueRepo,
        OcrQueueFileRepository $ocrQueueFileRepo,
        SubjectRepository $subjectRepo,
    ) {
        $this->ocrQueueRepo = $ocrQueueRepo;
        $this->ocrQueueFileRepo = $ocrQueueFileRepo;
        $this->subjectRepo = $subjectRepo;
    }

    /**
     * Get subject count for ocr process.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return int
     */
    public function getSubjectCountForOcr(int $projectId, int $expeditionId = null): int
    {
        return $this->subjectRepo->getSubjectCountForOcr($projectId, $expeditionId);
    }

    /**
     * Create ocr queue record.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @param array $data
     * @return \App\Models\OcrQueue
     */
    public function createOcrQueue(int $projectId, int $expeditionId = null, array $data = []): OcrQueue
    {
        return $this->ocrQueueRepo->firstOrCreate([
            'project_id'    => $projectId,
            'expedition_id' => $expeditionId,
        ], $data);
    }

    /**
     * Create ocr queue files.
     *
     * @param int $queueId
     * @param int $projectId
     * @param int|null $expeditionId
     */
    public function createOcrQueueFiles(int $queueId, int $projectId, int $expeditionId = null): void
    {
        $cursor = $this->subjectRepo->getSubjectCursorForOcr($projectId, $expeditionId);

        $cursor->each(function ($subject) use ($queueId) {
            $attributes = [
                'queue_id'   => $queueId,
                'subject_id' => (string) $subject->_id,
                'access_uri' => $subject->accessURI,
            ];

            $this->ocrQueueFileRepo->firstOrCreate($attributes, $attributes);
        });
    }
}