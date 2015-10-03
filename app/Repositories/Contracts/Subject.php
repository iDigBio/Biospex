<?php namespace App\Repositories\Contracts;

interface Subject extends Repository
{
    public function getUnassignedCount($id);

    public function getSubjectIds($projectId, $take = null, $expeditionId = null);

    public function detachSubjects($ids = array(), $expeditionId);

    public function loadGridModel();

    public function getTotalNumberOfRows(array $filters = array());

    public function getRows($limit, $offset, $orderBy = null, $sord = null, array $filters = array());

    public function findByFilename($filename);

    public function findByProjectOccurrenceId($project_id, $occurrence_id);
}
