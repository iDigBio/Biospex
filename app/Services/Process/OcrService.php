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

use App\Notifications\OcrProcessComplete;
use App\Services\Model\OcrQueueService;
use App\Services\Csv\Csv;
use App\Services\Model\SubjectService;
use App\Services\MongoDbService;
use Illuminate\Support\LazyCollection;
use MongoDB\BSON\Regex;
use Storage;
use Str;

/**
 * Class OcrService
 *
 * @package App\Services\Process
 */
class OcrService
{
    /**
     * @var \App\Services\Model\OcrQueueService
     */
    private $ocrQueueService;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csvService;

    /**
     * @var
     */
    public $folderPath;

    /**
     * @var \App\Services\Model\SubjectService
     */
    private $subjectService;

    /**
     * Ocr constructor.
     *
     * @param \App\Services\Model\SubjectService $subjectService
     * @param \App\Services\Model\OcrQueueService $ocrQueueService
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        SubjectService $subjectService,
        OcrQueueService $ocrQueueService,
        MongoDbService $mongoDbService,
        Csv $csvService
    ) {
        $this->ocrQueueService = $ocrQueueService;
        $this->mongoDbService = $mongoDbService;
        $this->csvService = $csvService;
        $this->subjectService = $subjectService;
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
     * Find queue by id.
     *
     * @param int $id
     * @return mixed
     */
    public function findOcrQueueById(int $id)
    {
        return $this->ocrQueueService->find($id);
    }

    /**
     * Return ocr queue for command process.
     *
     * @return mixed
     */
    public function getOcrQueueForOcrProcessCommand()
    {
        return $this->ocrQueueService->getOcrQueueForOcrProcessCommand();
    }

    /**
     * Delete directory for queue.
     */
    public function deleteDir()
    {
        Storage::deleteDirectory($this->folderPath);
    }

    /**
     * Create ocr queue record.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return \App\Models\OcrQueue
     */
    public function createOcrQueue(int $projectId, int $expeditionId = null): \App\Models\OcrQueue
    {
        return $this->ocrQueueService->firstOrCreate(['project_id' => $projectId, 'expedition_id' => $expeditionId]);
    }

    /**
     * @param int $projectId
     * @param int|null $expeditionId
     * @param bool $error
     * @return \Illuminate\Support\LazyCollection
     */
    public function getSubjectCursorForOcr(int $projectId, int $expeditionId = null, bool $error = false): LazyCollection
    {
        return $this->subjectService->getSubjectCursorForOcr($projectId, $expeditionId, $error);
    }

    /**
     * Get subject count for ocr process.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @param bool $error
     * @return int
     */
    public function getSubjectCountForOcr(int $projectId, int $expeditionId = null, bool $error = false): int
    {
        return $this->subjectService->getSubjectCountForOcr($projectId, $expeditionId, $error);
    }

    /**
     * Send complete notification.
     *
     * @param \App\Models\OcrQueue $queue
     * @throws \League\Csv\CannotInsertRecord
     */
    public function complete(\App\Models\OcrQueue $queue)
    {
        $this->sendNotify($queue);
        $queue->delete();
        $this->deleteDir();
    }

    /**
     * Send notification for completed ocr process
     *
     * @param $queue
     * @throws \League\Csv\CannotInsertRecord
     */
    public function sendNotify(\App\Models\OcrQueue $queue)
    {
        $cursor = $this->subjectService->getSubjectCursorForOcr($queue->project_id, $queue->expedition_id, true);

        $subjects = $cursor->map(function ($subject) {
            return [
                'subject_id' => (string) $subject->_id,
                'url'        => $subject->accessURI,
                'ocr'        => $subject->ocr
            ];
        });

        $csvName = Str::random().'.csv';
        $fileName = $this->csvService->createReportCsv($subjects->toArray(), $csvName);

        $queue->project->group->owner->notify(new OcrProcessComplete($queue->project->title, $fileName));
    }
}