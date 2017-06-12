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

    public function getUnassignedCount($id);

    public function getSubjectIds($projectId, $take = null, $expeditionId = null);

    public function detachSubjects($ids = [], $expeditionId);

    public function getTotalNumberOfRows($filters = [], $route, $projectId, $expeditionId = null);

    public function getRows($limit, $offset, $orderBy = null, $sord = null, $filters = []);
}