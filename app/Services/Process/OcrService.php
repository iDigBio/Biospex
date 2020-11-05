<?php
/**
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
use App\Services\MongoDbService;
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
     * Ocr constructor.
     *
     * @param \App\Services\Model\OcrQueueService $ocrQueueService
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        OcrQueueService $ocrQueueService,
        MongoDbService $mongoDbService,
        Csv $csvService
    ) {
        $this->ocrQueueService = $ocrQueueService;
        $this->mongoDbService = $mongoDbService;
        $this->csvService = $csvService;
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
     * Set query for count and retrieving subjects that need to be processed.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @param \MongoDB\BSON\Regex|null $regex
     * @return array
     */
    private function setSubjectQuery(int $projectId, int $expeditionId = null, Regex $regex = null): array
    {
        return $query = null === $expeditionId ? [
            'project_id' => $projectId,
            'ocr'        => $regex ?: '',
        ] : [
            'project_id'     => $projectId,
            'expedition_ids' => $expeditionId,
            'ocr'            => $regex ?: '',
        ];
    }

    /**
     * Get subject count.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return int
     */
    public function getSubjectCount(int $projectId, int $expeditionId = null): int
    {
        $query = $this->setSubjectQuery($projectId, $expeditionId);

        $this->mongoDbService->setCollection('subjects');

        return $this->mongoDbService->count($query);
    }

    /**
     * Get subjects that need processing.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     * @return array
     */
    public function getSubjectsToProcess(int $projectId, int $expeditionId = null): array
    {
        $query = $this->setSubjectQuery($projectId, $expeditionId);

        $this->mongoDbService->setCollection('subjects');

        $results = $this->mongoDbService->find($query);

        $subjects = [];
        foreach ($results as $doc) {
            $subjects[] = [
                'subject_id' => (string) $doc['_id'],
                'url'        => $doc['accessURI'],
            ];
        }

        return $subjects;
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
        $query = $this->setSubjectQuery($queue->project_id, $queue->expedition_id, $this->mongoDbService->setRegex('^Error:'));
        $this->mongoDbService->setCollection('subjects');
        $results = $this->mongoDbService->find($query);

        $subjects = [];
        foreach ($results as $doc) {
            $subjects[] = [
                'subject_id' => (string) $doc['_id'],
                'url'        => $doc['accessURI'],
                'ocr'        => $doc['ocr'],
            ];
        }

        $csvName = Str::random().'.csv';
        $fileName = $this->csvService->createReportCsv($subjects, $csvName);

        $queue->project->group->owner->notify(new OcrProcessComplete($queue->project->title, $fileName));
    }
}