<?php

namespace App\Services\Model;

use App\Interfaces\OcrQueue;
use App\Jobs\BuildOcrBatchesJob;

class OcrQueueService
{

    /**
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * OcrQueueService constructor.
     * @param OcrQueue $ocrQueueContract
     */
    public function __construct(OcrQueue $ocrQueueContract)
    {

        $this->ocrQueueContract = $ocrQueueContract;
    }

    /**
     * Process Ocr.
     *
     * @param $projectId
     * @param null $expeditionId
     * @return bool
     */
    public function processOcr($projectId, $expeditionId = null)
    {
        $queueCheck = $this->ocrQueueContract->findBy('project_id', $projectId);

        if ($queueCheck === null)
        {
            BuildOcrBatchesJob::dispatch($projectId, $expeditionId);

            return true;
        }

        return false;
    }
}