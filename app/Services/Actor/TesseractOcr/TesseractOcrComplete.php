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
use App\Repositories\OcrQueueFileRepository;
use App\Repositories\SubjectRepository;
use App\Services\Process\CreateReportService;
use Str;

/**
 * Class OcrService
 */
class TesseractOcrComplete
{
    use ButtonTrait;

    private SubjectRepository $subjectRepo;

    private CreateReportService $createReportService;

    private OcrQueueFileRepository $ocrQueueFileRepo;

    /**
     * Ocr constructor.
     */
    public function __construct(
        SubjectRepository $subjectRepo,
        OcrQueueFileRepository $ocrQueueFileRepo,
        CreateReportService $createReportService
    ) {
        $this->subjectRepo = $subjectRepo;
        $this->createReportService = $createReportService;
        $this->ocrQueueFileRepo = $ocrQueueFileRepo;
    }

    /**
     * Ocr process completed.
     *
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
     */
    public function updateSubjects(int $queueId): void
    {
        $cursor = $this->ocrQueueFileRepo->getOcrQueueFileQuery($queueId);

        $cursor->each(function ($file) {
            $filePath = config('zooniverse.directory.lambda-ocr').'/'.$file->subject_id.'.txt';
            $content = \Storage::disk('s3')->get($filePath);
            $ocrText = trim(preg_replace('/\s+/', ' ', trim($content)));
            $this->subjectRepo->update(['ocr' => $ocrText], $file->subject_id);
        });
    }

    /**
     * Send notification for completed ocr process.
     *
     * @throws \League\Csv\CannotInsertRecord
     */
    public function sendNotify(OcrQueue $queue): void
    {
        $queue->load('project.group.owner');

        $cursor = $this->subjectRepo->getSubjectCursorForOcr($queue->project_id, $queue->expedition_id);
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
