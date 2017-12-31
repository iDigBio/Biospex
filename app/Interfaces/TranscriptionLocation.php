<?php

namespace App\Interfaces;

interface TranscriptionLocation extends Eloquent
{

    /**
     * @param $id
     * @return mixed
     */
    public function getStateGroupByCountByProjectId($id);

    /**
     * @param $id
     * @return mixed
     */
    public function getTranscriptionFusionTableData($id);
}