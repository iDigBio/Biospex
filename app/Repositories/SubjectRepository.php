<?php namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class SubjectRepository extends Repository implements Subject, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Subject::class;
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
}
