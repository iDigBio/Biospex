<?php

namespace App\Services\Actor;

use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\OcrQueue;
use App\Services\MongoDbService;

class OcrCreate extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFile;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var array
     */
    private $fileData = [];

    /**
     * OcrCreate constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \App\Repositories\Interfaces\OcrFile $ocrFile
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        OcrQueue $ocrQueue,
        OcrFile $ocrFile,
        MongoDbService $mongoDbService
    ) {
        $this->ocrQueue = $ocrQueue;
        $this->ocrFile = $ocrFile;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Process Project/Expedition for OCR file creation.
     * MyModel::truncate();
     *
     * @param $projectId
     * @param null $expeditionId
     * @return bool
     * @throws \Exception
     */
    public function create($projectId, $expeditionId = null)
    {
        $queue = $this->ocrQueue->firstOrCreate(['project_id' => $projectId, 'expedition_id' => $expeditionId]);
        $queue->ocrFiles()->delete();

        $this->buildOcrSubjectsArray($queue->id, $projectId, $expeditionId);

        $total = count($this->fileData);

        if ($total === 0) {
            $queue->delete();

            return false;
        }

        $queue->ocrFiles()->createMany($this->fileData);

        $queue->total = $total;
        $queue->save();

        return true;
    }

    /**
     * Build the ocr subject array.
     *
     * @param $queueId
     * @param $projectId
     * @param null $expeditionId
     */
    protected function buildOcrSubjectsArray($queueId, $projectId, $expeditionId = null)
    {
        $query = null === $expeditionId ? [
            'project_id' => (int) $projectId,
            'ocr'        => '',
        ] : ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId, 'ocr' => ''];

        $this->mongoDbService->setCollection('subjects');
        $results = $this->mongoDbService->find($query);

        foreach ($results as $doc) {
            $this->fileData[] = [
                'queue_id'   => $queueId,
                'subject_id' => (string) $doc['_id'],
                'url'        => $doc['accessURI'],
            ];
        }
    }
}