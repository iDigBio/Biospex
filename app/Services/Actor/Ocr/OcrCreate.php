<?php

namespace App\Services\Actor\Ocr;

use App\Services\MongoDbService;
use App\Repositories\Interfaces\OcrQueue;

class OcrCreate extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var array
     */
    private $ocrData = [];

    /**
     * BuildOcrBatchesJob constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        OcrQueue $ocrQueue,
        MongoDbService $mongoDbService
    ) {
        $this->ocrQueue = $ocrQueue;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Process Project/Expedition for OCR file creation.
     *
     * @param $projectId
     * @param null $expeditionId
     * @return bool
     * @throws \Exception
     */
    public function create($projectId, $expeditionId = null)
    {
        $queue = $this->ocrQueue->firstOrCreate(['project_id' => $projectId, 'expedition_id' => $expeditionId]);

        $this->mongoDbService->setCollection('ocr_files');
        $this->mongoDbService->deleteMany(['project_id' => (int) $projectId,'ocr' => '']);

        $this->buildOcrSubjectsArray($queue->id, $projectId, $expeditionId);

        $total = count($this->ocrData);

        if ($total === 0) {
            $queue->delete();
            return false;
        }

        $this->mongoDbService->setCollection('ocr_files');
        $this->mongoDbService->insertMany($this->ocrData);
        $this->ocrQueue->update(['total' => $total], $queue->id);

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
            $this->ocrData[] = [
                'queue_id' => $queueId,
                'subject_id' => (string) $doc['_id'],
                'crop'     => config('config.ocr_crop'),
                'messages' => '',
                'ocr'      => '',
                'status'   => 'pending',
                'url'      => $doc['accessURI']
            ];
        }
    }
}