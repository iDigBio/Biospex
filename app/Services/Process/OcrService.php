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

namespace App\Services\Process;

use App\Models\OcrQueue;
use App\Models\Subject;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use App\Repositories\OcrQueueRepository;
use App\Repositories\SubjectRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Storage;
use Str;

/**
 * Class OcrService
 *
 * @package App\Services\Process
 */
class OcrService
{
    use ButtonTrait;

    /**
     * @var \App\Repositories\OcrQueueRepository
     */
    private OcrQueueRepository $ocrQueueRepo;

    /**
     * @var string
     */
    public string $folderPath;

    /**
     * @var \App\Repositories\SubjectRepository
     */
    private SubjectRepository $subjectRepo;

    /**
     * @var \App\Services\Process\CreateReportService
     */
    private CreateReportService $createReportService;

    /**
     * Ocr constructor.
     *
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Repositories\OcrQueueRepository $ocrQueueRepo
     * @param \App\Services\Process\CreateReportService $createReportService
     */
    public function __construct(
        SubjectRepository $subjectRepo,
        OcrQueueRepository $ocrQueueRepo,
        CreateReportService $createReportService
    ) {
        $this->ocrQueueRepo = $ocrQueueRepo;
        $this->subjectRepo = $subjectRepo;
        $this->createReportService = $createReportService;
    }

    /**
     * Create directory for queue.
     *
     * @param $queueId
     */
    public function setDir($queueId)
    {
        $this->folderPath = 'ocr/'.$queueId.'-'.md5($queueId);

        if (! Storage::exists($this->folderPath)) {
            Storage::makeDirectory($this->folderPath);
        }
    }

    /**
     * Delete directory for queue.
     */
    public function deleteDir()
    {
        Storage::deleteDirectory($this->folderPath);
    }

    /**
     * Find queue by id where error is 0.
     *
     * @param int $id
     * @return mixed
     */
    public function findOcrQueueById(int $id): mixed
    {
        return $this->ocrQueueRepo->findWith($id, ['project.group.owner']);
    }

    /**
     * Return ocr queue for command process.
     *
     * @param bool $reset
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
     */
    public function getFirstQueue(bool $reset = false): Model|Builder|null
    {
        return $this->ocrQueueRepo->getFirstQueue($reset);
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
        return $this->ocrQueueRepo->firstOrCreate(['project_id' => $projectId, 'expedition_id' => $expeditionId], $data);
    }

    /**
     * Return query for processing subjects in ocr.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     *
     * @return \App\Models\Subject|\Illuminate\Database\Eloquent\Builder
     */
    public function getSubjectQueryForOcr(int $projectId, int $expeditionId = null): Builder|Subject
    {
        return $this->subjectRepo->getSubjectQueryForOcr($projectId, $expeditionId);
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
     * Send notification for completed ocr process.
     *
     * @param \App\Models\OcrQueue $queue
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function sendNotify(OcrQueue $queue): void
    {
        $cursor = $this->subjectRepo->getSubjectErrorCursorForOcr($queue->project_id, $queue->expedition_id);

        $subjects = $cursor->map(function ($subject) {
            return [
                'subject_id' => (string) $subject->_id,
                'url'        => $subject->accessURI,
                'ocr'        => $subject->ocr
            ];
        });

        $csvName = Str::random().'.csv';
        $fileName = $this->createReportService->createCsvReport($csvName, $subjects->toArray());
        $button = [];
        if ($fileName !== null) {
            $route = route('admin.downloads.report', ['file' => $fileName]);
            $button = $this->createButton($route, t('View Rejected Records'), 'error');
        }

        $attributes = [
            'subject' => t('Ocr Process Complete'),
            'html'    => [
                t('The OCR processing of your data is complete for %s.', $queue->project->title)
            ],
            'buttons' => $button
        ];

        $queue->project->group->owner->notify(new Generic($attributes));
    }
}