<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryContract;

/**
 * Class BaseEloquentRepository
 * @package App\Repositories\Eloquent
 */
class BaseEloquentRepository extends EloquentRepository
{
    /**
     * @inheritdoc
     */
    public function findById($id, array $attributes = ['*'])
    {
        return $this->find($id, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findAllWithRelations(array $withRelations = [], array $attributes = ['*'])
    {
        return $this->with($withRelations)->findAll($attributes);
    }

    /**
     * @inheritdoc
     */
    public function findWithRelations($id, $relations, array $attributes = ['*'])
    {
        return $this->with($relations)->find($id, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findWhereWithRelations(array $where = [], array $relations = [], array $attributes = ['*'])
    {
        return $this->with($relations)->findWhere($where, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findWhereInHasRelationsWithRelations($attributeValues, array $hasRelations = [], array $relations = [], array $attributes = ['*'])
    {
        foreach ($hasRelations as $relation)
        {
            $this->has($relation);
        }

        return $this->with($relations)->findWhereIn($attributeValues, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findAllHasRelationsWithRelations(array $hasRelations = [], array $relations = [], array $attributes = ['*'])
    {
        foreach ($hasRelations as $relation)
        {
            $this->has($relation);
        }

        return $this->with($relations)->findAll($attributes);
    }

    /**
     * @inheritdoc
     */
    public function updateOrCreateRecord(array $attributes, array $values = [])
    {
        $this->updateOrCreate($attributes, $values);
    }
}