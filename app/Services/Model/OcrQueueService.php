<?php

namespace App\Services\Model;

use App\Interfaces\OcrQueue;
use App\Jobs\BuildOcrBatchesJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OcrQueueService
{
    use DispatchesJobs;

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
            $this->dispatch(new BuildOcrBatchesJob($project->id, $expeditionId));

            return true;
        }

        return false;
    }
}