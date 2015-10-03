<?php namespace App\Repositories;

use App\Repositories\Contracts\Subject;
use App\Models\Subject as Model;

class SubjectRepository extends Repository implements Subject
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getUnassignedCount($id)
    {
        return $this->model->getUnassignedCount($id);
    }

    public function getSubjectIds($projectId, $take = null, $expeditionId = null)
    {
        return $this->model->getSubjectIds($projectId, $take, $expeditionId);
    }

    /**
     * Detach subjects
     *
     * @param array $ids
     * @param $expeditionId
     * @return mixed
     */
    public function detachSubjects($ids = [], $expeditionId)
    {
        return $this->model->detachSubjects($ids, $expeditionId);
    }

    /**
     * Load grid model for jqGrid.
     */
    public function loadGridModel()
    {
        return $this->model->loadGridModel();
    }

    /**
     * Grid: get total number of rows.
     *
     * @param array $filters
     * @return int
     */
    public function getTotalNumberOfRows(array $filters = [])
    {
        return $this->model->getTotalNumberOfRows($filters);
    }

    /**
     * Grid: get rows.
     *
     * @param $limit
     * @param $offset
     * @param null $orderBy
     * @param null $sord
     * @param bool $initial
     * @param array $filters
     * @return array
     */
    public function getRows($limit, $offset, $orderBy = null, $sord = null, array $filters = [])
    {
        return $this->model->getRows($limit, $offset, $orderBy, $sord, $filters);
    }

    /**
     * @param $filename
     * @return mixed
     */
    public function findByFilename($filename)
    {
        return $this->model->findByFilename($filename);
    }

    /**
     * @param $project_id
     * @param $occurrence_id
     * @return mixed
     */
    public function findByProjectOccurrenceId($project_id, $occurrence_id)
    {
        return $this->model->findByProjectOccurrenceId($project_id, $occurrence_id);
    }
}
