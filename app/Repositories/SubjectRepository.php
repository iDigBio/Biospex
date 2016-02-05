<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Subject;
use Biospex\Models\Subject as Model;

class SubjectRepository extends Repository implements Subject
{
    /**
     * SubjectRepository constructor.
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
     * @param $projectId
     * @param null $expeditionId
     * @param null $route
     * @return int
     */
    public function getTotalNumberOfRows($filters = [], $route, $projectId, $expeditionId = null)
    {
        return $this->model->getTotalNumberOfRows($filters, $route, $projectId, $expeditionId);
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
    public function getRows($limit, $offset, $orderBy = null, $sord = null, $filters = [])
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
     * Find subjects by project id.
     * @param $project_id
     * @return mixed
     */
    public function findByProjectId($project_id)
    {
        return $this->model->findByProjectId($project_id);
    }

    /**
     * Find subjects by project id and empty ocr.
     * @param $project_id
     * @return mixed
     */
    public function findByProjectIdOcr($project_id)
    {
        return $this->model->findByProjectIdOcr($project_id);
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
