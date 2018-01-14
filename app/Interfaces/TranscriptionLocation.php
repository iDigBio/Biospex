<?php

namespace App\Interfaces;

interface TranscriptionLocation extends Eloquent
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