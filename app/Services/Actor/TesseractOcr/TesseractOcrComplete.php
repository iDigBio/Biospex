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
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Models\OcrQueueFile;
use App\Services\Models\SubjectModelService;
use App\Services\Process\CreateReportService;
use Str;

/**
 * Class OcrService
 *
 * @package App\Services\Process
 */
class TesseractOcrComplete
{
    use ButtonTrait;

    /**
     * Ocr constructor.
     *
     * @param \App\Services\Models\SubjectModelService $subjectModelService
     * @param \App\Models\OcrQueueFile $ocrQueueFile
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function __construct(
        private SubjectModelService $subjectModelService,
        private OcrQueueFile $ocrQueueFile,
        private CreateReportService $createReportService
    ) {}

    /**
     * Ocr process completed.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function ocrCompleted(OcrQueue $ocrQueue): void
    {
        $this->updateSubjects($ocrQueue->id);

        $this->sendNotify($ocrQueue);

        $ocrQueue->delete();

        $files = \Storage::disk('s3')->allFiles(config('zooniverse.directory.lambda-ocr'));
        \Storage::disk('s3')->delete($files);
    }

    /**
     * Update subjects with ocr result.
     *
     * @param int $queueId
     * @return void
     */
    public function updateSubjects(int $queueId): void
    {
        $cursor = $this->ocrQueueFile->where('queue_id', $queueId)->cursor();

        $cursor->each(function ($file) use ($queueId) {
            $filePath = config('zooniverse.directory.lambda-ocr') . '/' . $file->subject_id . '.txt';
            $content = \Storage::disk('s3')->get($filePath);
            $ocrText = trim(preg_replace('/\s+/', ' ', trim($content)));
            $this->subjectModelService->update(['ocr' => $ocrText], $file->subject_id);
        });
    }

    /**
     * Send notification for completed ocr process.
     *
     * @param \App\Models\OcrQueue $queue
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function sendNotify(OcrQueue $queue): void
    {
        $queue->load('project.group.owner');

        $cursor = $this->subjectModelService->getSubjectCursorForOcr($queue->project_id, $queue->expedition_id);
        $subjects = $cursor->map(function ($subject) {
            return [
                'subject_id' => $subject->_id,
                'url'        => $subject->accessURI,
                'ocr'        => $subject->ocr,
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
            'html'    => [
                t('The OCR processing of your data is complete for %s.', $queue->project->title),
            ],
            'buttons' => $button,
        ];

        $queue->project->group->owner->notify(new Generic($attributes));
    }

}