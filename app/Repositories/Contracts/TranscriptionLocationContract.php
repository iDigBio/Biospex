<?php

namespace App\Repositories\Contracts;

interface TranscriptionLocationContract extends RepositoryContract, CacheableContract
{

    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreateRecord(array $attributes, array $values = []);

    /**
     * @param $id
     * @return mixed
     */
    public function getStateCountyGroupByCountByProjectId($id);

    public function getTranscriptionFusionTableData($id);
}