<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryContract
{
    /**
     * Find by id.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function findById($id, array $attributes = ['*']);

    /**
     * Find all with relations.
     *
     * @param array $withRelations
     * @param array $attributes
     * @return mixed
     */
    public function findAllWithRelations(array $withRelations = [], array $attributes = ['*']);

    /**
     * Find with relations.
     *
     * @param integer $id
     * @param array|string $relations
     * @param array $attributes
     * @return mixed
     */
    public function findWithRelations($id, $relations, array $attributes = ['*']);

    /**
     * Find where with relations.
     *
     * @param array $where
     * @param array $relations
     * @param array $attributes
     * @return \Illuminate\Support\Collection|mixed
     */
    public function findWhereWithRelations(array $where = [], array $relations = [], array $attributes = ['*']);
    /**
     * Find expeditions where in, having relations, with relations.
     *
     * @param $attributeValues
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findWhereInHasRelationsWithRelations($attributeValues, array $hasRelations = [], array $relations = [], array $attributes = ['*']);

    /**
     * Find all having relations and with relations.
     *
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findAllHasRelationsWithRelations(array $hasRelations = [], array $relations = [], array $attributes = ['*']);

    /**
     * Update or create a record.
     *
     * @param array $attributes
     * @param array $values
     */
    public function updateOrCreateRecord(array $attributes, array $values = []);
}