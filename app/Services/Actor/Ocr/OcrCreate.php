<?php

namespace App\Services\Actor\Ocr;

use App\Repositories\Interfaces\OcrFile;
use App\Services\MongoDbService;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\Interfaces\OcrQueue;

class OcrCreate extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFile;

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueue;

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var
     */
    private $ocrData;

    /**
     * BuildOcrBatchesJob constructor.
     *
     * @param \App\Repositories\Interfaces\OcrFile $ocrFile
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueue
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(
        OcrFile $ocrFile,
        OcrQueue $ocrQueue,
        MongoDbService $mongoDbService
    ) {
        $this->ocrFile = $ocrFile;
        $this->ocrQueue = $ocrQueue;
        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Process Project/Expedition for OCR file creation.
     *
     * @param $projectId
     * @param null $expeditionId
     * @return bool
     */
    public function create($projectId, $expeditionId = null)
    {
        $this->buildOcrSubjectsArray($projectId, $expeditionId);
        $total = count($this->ocrData['subjects']);

        if ($total === 0) {
            return false;
        }

        $this->ocrData['project_id'] = $projectId;
        $this->ocrData['expedition_id'] = $expeditionId;
        $this->ocrData['header'] = [
            'processed' => 0,
            'status'    => 'pending',
            'total'     => $total,
        ];

        $ocrFile = $this->ocrFile->create($this->ocrData);

        return $this->ocrQueue->create([
            'project_id'    => $projectId,
            'expedition_id' => $expeditionId,
            'mongo_id'      => $ocrFile->_id,
            'total'         => $total,
        ]);
    }

    /**
     * Build the ocr subject array.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    protected function buildOcrSubjectsArray($projectId, $expeditionId = null)
    {
        $this->mongoDbService->setCollection('subjects');
        $query = null === $expeditionId ? [
            'project_id' => (int) $projectId,
            'ocr'        => '',
        ] : ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId, 'ocr' => ''];

        $results = $this->mongoDbService->find($query);

        foreach ($results as $doc) {
            $this->buildOcrQueueData($doc);
        }
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $doc
     */
    protected function buildOcrQueueData($doc)
    {
        $this->ocrData['subjects'][(string) $doc['_id']] = [
            'crop'     => config('config.ocr_crop'),
            'messages' => '',
            'ocr'      => '',
            'status'   => 'pending',
            'url'      => $doc['accessURI'],
        ];
    }
}