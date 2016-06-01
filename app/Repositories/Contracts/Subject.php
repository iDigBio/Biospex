<?php namespace App\Repositories\Contracts;

interface Subject extends Repository
{
    public function getUnassignedCount($id);

    public function getSubjectIds($projectId, $take = null, $expeditionId = null);

    public function detachSubjects($ids = [], $expeditionId);

    public function loadGridModel();

    public function getTotalNumberOfRows($filters = [], $route, $projectId, $expeditionId = null);

    public function getRows($limit, $offset, $orderBy = null, $sord = null, $filters = []);
}
