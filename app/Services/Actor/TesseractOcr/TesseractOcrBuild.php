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
use App\Models\Project;
use App\Notifications\Traits\ButtonTrait;
use App\Nova\Expedition;
use App\Services\Subject\SubjectService;

/**
 * Class OcrService
 */
class TesseractOcrBuild
{
    use ButtonTrait;

    /**
     * Ocr constructor.
     */
    public function __construct(
        protected OcrQueue $ocrQueue,
        protected OcrQueueFile $ocrQueueFile,
        protected SubjectService $subjectService,
    ) {}

    /**
     * Get subject count for ocr process.
     */
    public function getSubjectCountForOcr(Project $project, ?Expedition $expedition = null): int
    {
        return $this->subjectService->getSubjectCountForOcr($project, $expedition);
    }

    /**
     * Create ocr queue record.
     */
    public function createOcrQueue(Project $project, ?Expedition $expedition = null, array $data = []): OcrQueue
    {
        return $this->ocrQueue->firstOrCreate([
            'project_id' => $project->id,
            'expedition_id' => $expedition->id,
        ], $data);
    }

    /**
     * Create ocr queue files.
     */
    public function createOcrQueueFiles(OcrQueue $queue, Project $project, ?Expedition $expedition = null): void
    {
        $cursor = $this->subjectService->getSubjectCursorForOcr($project, $expedition);

        $cursor->each(function ($subject) use ($queue) {
            $attributes = [
                'queue_id' => $queue->id,
                'subject_id' => (string) $subject->_id,
                'access_uri' => $subject->accessURI,
            ];

            $this->ocrQueueFile->firstOrCreate($attributes, $attributes);
        });
    }
}
