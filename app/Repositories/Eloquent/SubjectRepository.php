<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectContract;
use Illuminate\Contracts\Container\Container;

class SubjectRepository extends EloquentRepository implements SubjectContract
{
    /**
     * SubjectRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Subject::class)
            ->setRepositoryId('biospex.repository.subject');
    }

    /**
     * @inheritdoc
     */
    public function findSubjectsByExpeditionId($expeditionId, array $attributes = ['*'])
    {
        return $this->findWhere(['expedition_ids', '=', $expeditionId]);
    }

    public function getUnassignedCount($id)
    {
        return $this->loadUnassignedCount($id);
    }

    public function getSubjectIds($projectId, $take = null, $expeditionId = null)
    {
        return $this->loadSubjectIds($projectId, $take, $expeditionId);
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
        return $this->getModel()->detachSubjects($ids, $expeditionId);
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
        return $this->getNumberOfRowsTotal($filters, $route, $projectId, $expeditionId);
    }

    /**
     * Grid: get rows.
     *
     * @param $limit
     * @param $offset
     * @param null $orderBy
     * @param null $sord
     * @param array $filters
     * @return mixed
     */
    public function getRows($limit, $offset, $orderBy = null, $sord = null, $filters = [])
    {
        return $this->getAllRows($limit, $offset, $orderBy, $sord, $filters);
    }
}