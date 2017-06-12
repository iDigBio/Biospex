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

    /**
     * @inheritdoc
     */
    public function getUnassignedCount($id)
    {
        return $this->loadUnassignedCount($id);
    }

    /**
     * @inheritdoc
     */
    public function getSubjectIds($projectId, $take = null, $expeditionId = null)
    {
        return $this->loadSubjectIds($projectId, $take, $expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function detachSubjects($ids = [], $expeditionId)
    {
        return $this->detachAllSubjects($ids, $expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function getTotalRowCount(array $vars = [])
    {
        return $this->getRowCount($vars);
    }

    /**
     * @inheritdoc
     */
    public function getRows(array $vars = [])
    {
        return $this->getAllRows($vars);
    }
}