<?php

namespace App\Repositories\Contracts;

interface TranscriptionLocationContract extends RepositoryContract, CacheableContract
{

    /**
     * @param $id
     * @return mixed
     */
    public function getStateCountyGroupByCountByProjectId($id);

    public function getTranscriptionFusionTableData($id);
}