<?php

namespace App\Repositories\Contracts;

interface SubjectContract extends RepositoryContract, CacheableContract
{

    /**
     * Find subjects by expedition id.
     *
     * @param $expeditionId
     * @param array $attributes
     * @return mixed
     */
    public function findSubjectsByExpeditionId($expeditionId, array $attributes = ['*']);
}