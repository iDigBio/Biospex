<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface TranscriptionLocation extends RepositoryInterface
{

    /**
     * GEt county data for mapping transcriptions.
     *
     * @param $projectId
     * @param $stateId
     * @return mixed
     */
    public function getCountyData($projectId, $stateId);
}