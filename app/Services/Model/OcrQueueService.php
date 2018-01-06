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
     * @param $project
     * @param null $expeditionId
     * @return bool
     */
    public function processOcr($project, $expeditionId = null)
    {
        $queueCheck = $this->ocrQueueContract->findBy('project_id', $project->id);

        if ($queueCheck === null)
        {
            BuildOcrBatchesJob::dispatch($project->id, $expeditionId);

            return true;
        }

        return false;
    }
}