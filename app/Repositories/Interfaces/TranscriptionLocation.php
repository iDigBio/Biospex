<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface TranscriptionLocation extends RepositoryInterface
{

    /**
     * @param $projectId
     * @return mixed
     */
    public function getStateGroupByCountByProjectId($projectId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getTranscriptionFusionTableData($projectId);
}