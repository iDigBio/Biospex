<?php

namespace App\Repositories\Traits;

use App\Repositories\Eloquent\EloquentRepository;

/**
 * Class EloquentRepositoryCommon
 * @package App\Repositories\Traits
 */
trait EloquentRepositoryCommon
{

    /**
     * Find by id.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function findById($id, array $attributes = ['*'])
    {
        return $this->find($id, $attributes);
    }

    /**
     * Find all with relations.
     *
     * @param array $withRelations
     * @param array $attributes
     * @return mixed
     */
    public function findAllWithRelations(array $withRelations = [], array $attributes = ['*'])
    {
        return $this->with($withRelations)->findAll($attributes);
    }

    /**
     * Find with relations.
     *
     * @param integer $id
     * @param array|string $relations
     * @return mixed
     */
    public function findWithRelations($id, $relations)
    {
        return $this->with($relations)->find($id);
    }

    /**
     * Find where with relations.
     *
     * @param array $where
     * @param array $relations
     * @param array $attributes
     * @return \Illuminate\Support\Collection|mixed
     */
    public function findWhereWithRelations(array $where = [], array $relations = [], array $attributes = [])
    {
        return $this->with($relations)->findWhere($where, $attributes);
    }

    /**
     * Find expeditions where in, having relations, with relations.
     *
     * @param $attributeValues
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
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
     * Find all having relations and with relations.
     *
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
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
     * Update or create a record.
     *
     * @param array $attributes
     * @param array $values
     */
    public function updateOrCreateRecord(array $attributes, array $values = [])
    {
        $this->updateOrCreate($attributes, $values);
    }
}