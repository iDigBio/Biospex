<?php

namespace App\Services\Google;

use App\Repositories\Contracts\TranscriptionLocationContract;
use App\Services\BaseService;

class FusionTableService extends BaseService
{

    /**
     * @var TranscriptionLocationContract
     */
    private $transcriptionLocationContract;

    /**
     * FusionTableService constructor.
     * @param TranscriptionLocationContract $transcriptionLocationContract
     */
    public function __construct(TranscriptionLocationContract $transcriptionLocationContract)
    {
        $this->transcriptionLocationContract = $transcriptionLocationContract;
    }

    /**
     * Get project locations.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectLocations($projectId)
    {
        return $this->transcriptionLocationContract->setCacheLifetime(0)
            ->getTranscriptionFusionTableData($projectId);
    }
}