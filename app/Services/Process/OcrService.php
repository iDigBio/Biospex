<?php

namespace App\Services\Process;

use App\Jobs\OcrTesseractJob;
use App\Notifications\OcrProcessComplete;
use App\Repositories\Interfaces\OcrQueue;
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use MongoDB\BSON\Regex;
use Storage;
use Str;

class OcrService
{
    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueueContract;

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
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueueContract
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        MongoDbService $mongoDbService,
        Csv $csvService
    )
    {
        $this->ocrQueueContract = $ocrQueueContract;
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
        return $this->ocrQueueContract->firstOrCreate(['project_id' => $projectId, 'expedition_id' => $expeditionId]);
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
                'ocr' => $doc['ocr']
            ];
        }

        if (count($subjects) > 0) {
            $csv = Storage::path(config('config.reports_dir')).'/'.Str::random().'.csv';
            $this->csvService->writerCreateFromPath($csv);
            $headers = array_keys($subjects[0]);
            $this->csvService->insertOne($headers);
            $this->csvService->insertAll($subjects);
        }

        $queue->project->group->owner->notify(new OcrProcessComplete($queue->project->title, $csv));
    }
}