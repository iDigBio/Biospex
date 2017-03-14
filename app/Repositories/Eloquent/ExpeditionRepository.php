<?php

namespace App\Repositories\Eloquent;

use App\Models\Expedition;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Contracts\Container\Container;

class ExpeditionRepository extends EloquentRepository implements ExpeditionContract
{

    /**
     * ExpeditionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Expedition::class)
            ->setRepositoryId('biospex.repository.expedition');

    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsForNfnClassificationProcess($ids = null, array $attributes = ['*'])
    {
        $this->with(['stat'])->has('nfnWorkflow')
            ->whereHas('actors', function ($query)
            {
                $query->where('completed', 0);
            }, '=');

        return $ids === null ? $this->findAll($attributes) : $this->findWhereIn(['id', $ids]);
    }

    /**
     * @inheritdoc
     */
    public function expeditionsHasRelations($relations, array $attributes = ['*'])
    {
        foreach ($relations as $relation)
        {
            $this->has($relation);
        }

        return $this->findAll($attributes);
    }

    /**
     * @inheritdoc
     */
    public function expeditionFindWith($id, $relations)
    {
        return $this->with($relations)->find($id);
    }

    /**
     * @inheritdoc
     */
    public function expeditionsHasRelationWhereIn($relation, $attributeValues, array $attributes = ['*'])
    {
        return $this->has($relation)->findWhereIn($attributeValues, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($id)
    {
        return $this->find($id)->subjects()->count();
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations =[])
    {
        return $this->with($relations)
            ->findWhereHas(['project.group.users', function ($query) {
                $query->where('user_id', 1);
            }]);
    }
}
