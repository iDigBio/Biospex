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

namespace App\Services\Actor\TesseractOcr;

use App\Models\OcrQueue;
use App\Models\OcrQueueFile;
use App\Notifications\Generic;
use App\Services\Process\CreateReportService;
use App\Services\Subject\SubjectService;
use App\Traits\ButtonTrait;
use Illuminate\Support\Str;

/**
 * Service for handling completion of Tesseract OCR processing tasks.
 * Manages post-processing tasks including email notifications and cleanup.
 */
class TesseractOcrCompletionService
{
    use ButtonTrait;

    /**
     * @param  OcrQueueFile  $ocrQueueFile  The OCR queue file model
     * @param  SubjectService  $subjectService  Service for handling subject-related operations
     * @param  CreateReportService  $createReportService  Service for creating reports
     */
    public function __construct(
        protected OcrQueueFile $ocrQueueFile,
        protected SubjectService $subjectService,
        protected CreateReportService $createReportService
    ) {}

    /**
     * Completes the OCR processing task and performs cleanup.
     *
     * @param  OcrQueue  $queue  The OCR queue entry to complete
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function complete(OcrQueue $queue): void
    {
        $this->sendCompletionEmail($queue);
        $queue->delete();
    }

    /**
     * Sends a completion notification email to a project owner.
     * Includes OCR results report and download button if available.
     *
     * @param  OcrQueue  $queue  The OCR queue entry containing project and expedition information
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function sendCompletionEmail(OcrQueue $queue): void
    {
        $queue->load('project.group.owner', 'expedition');

        $cursor = $this->subjectService->getSubjectCursorForOcr($queue->project, $queue->expedition);
        $subjects = $cursor->map(fn ($subject) => [
            'subject_id' => $subject->_id,
            'url' => $subject->accessURI,
            'ocr' => $subject->ocr,
        ]);

        $csvName = Str::random().'.csv';
        $fileName = $this->createReportService->createCsvReport($csvName, $subjects->toArray());

        $button = [];
        if ($fileName) {
            $route = route('admin.downloads.report', ['file' => $fileName]);
            $button = $this->createButton($route, t('View OCR Errors'), 'error');
        }

        $attributes = [
            'subject' => t('OCR Process Complete'),
            'html' => [t('The OCR processing of your data is complete for %s.', $queue->project->title)],
            'buttons' => $button,
        ];

        $queue->project->group->owner->notify(new Generic($attributes));
    }
}
