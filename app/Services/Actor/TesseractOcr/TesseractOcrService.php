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
use App\Notifications\Traits\ButtonTrait;
use App\Services\Process\CreateReportService;
use App\Services\Subject\SubjectService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TesseractOcrService
{
    use ButtonTrait;

    /**
     * OcrService constructor.
     */
    public function __construct(
        protected OcrQueue $ocrQueue,
        protected OcrQueueFile $ocrQueueFile,
        protected SubjectService $subjectService,
        protected CreateReportService $createReportService
    ) {}

    /**
     * Return ocr queue for a command process.
     */
    public function getFirstQueue(bool $reset = false): ?OcrQueue
    {
        if (! $reset && $this->ocrQueue->queued(1)->error(0)->exists()) {
            return null;
        }

        return $reset ?
            $this->ocrQueue->orderBy('id')->first() :
            $this->ocrQueue->queued(0)->error(0)->filesReady(1)->orderBy('id')->first();
    }

    /**
     * Ocr process completed.
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function ocrCompleted(OcrQueue $ocrQueue): void
    {
        $this->sendNotify($ocrQueue);
        $ocrQueue->delete();
    }

    /**
     * Send notification for completed ocr process.
     *
     * @throws \League\Csv\CannotInsertRecord|\League\Csv\Exception
     */
    public function sendNotify(OcrQueue $queue): void
    {
        $queue->load('project.group.owner', 'expedition');

        $cursor = $this->subjectService->getSubjectCursorForOcr($queue->project, $queue->expedition);
        $subjects = $cursor->map(function ($subject) {
            return [
                'subject_id' => $subject->_id,
                'url' => $subject->accessURI,
                'ocr' => $subject->ocr,
            ];
        });

        $csvName = Str::random().'.csv';
        $fileName = $this->createReportService->createCsvReport($csvName, $subjects->toArray());
        $button = [];
        if ($fileName !== null) {
            $route = route('admin.downloads.report', ['file' => $fileName]);
            $button = $this->createButton($route, t('View OCR Errors'), 'error');
        }

        $attributes = [
            'subject' => t('Ocr Process Complete'),
            'html' => [
                t('The OCR processing of your data is complete for %s.', $queue->project->title),
            ],
            'buttons' => $button,
        ];

        $queue->project->group->owner->notify(new Generic($attributes));
    }
}
