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
use App\Services\Models\SubjectModelService;

/**
 * Class OcrService
 */
readonly class TesseractOcrBuild
{
    use ButtonTrait;

    /**
     * Ocr constructor.
     */
    public function __construct(
        private OcrQueue $ocrQueue,
        private OcrQueueFile $ocrQueueFile,
        private SubjectModelService $subjectModelService,
    ) {}

    /**
     * Get subject count for ocr process.
     */
    public function getSubjectCountForOcr(int $projectId, ?int $expeditionId = null): int
    {
        return $this->subjectModelService->getSubjectCountForOcr($projectId, $expeditionId);
    }

    /**
     * Create ocr queue record.
     */
    public function createOcrQueue(int $projectId, ?int $expeditionId = null, array $data = []): OcrQueue
    {
        return $this->ocrQueue->firstOrCreate([
            'project_id' => $projectId,
            'expedition_id' => $expeditionId,
        ], $data);
    }

    /**
     * Create ocr queue files.
     */
    public function createOcrQueueFiles(int $queueId, int $projectId, ?int $expeditionId = null): void
    {
        $cursor = $this->subjectModelService->getSubjectCursorForOcr($projectId, $expeditionId);

        $cursor->each(function ($subject) use ($queueId) {
            $attributes = [
                'queue_id' => $queueId,
                'subject_id' => (string) $subject->_id,
                'access_uri' => $subject->accessURI,
            ];

            $this->ocrQueueFile->firstOrCreate($attributes, $attributes);
        });
    }
}
