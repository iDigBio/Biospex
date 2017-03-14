<?php

namespace App\Repositories\Contracts;

interface ExpeditionContract extends RepositoryContract, CacheableContract
{

    /**
     * Retrieve expeditions for Notes From Nature classification process.
     *
     * @param null $ids
     * @param array $attributes
     * @return mixed
     */
    public function getExpeditionsForNfnClassificationProcess($ids = null, array $attributes = ['*']);

    /**
     * Find all Expeditions having relations passed.
     *
     * @param array $relations
     * @param array $attributes
     * @return \Illuminate\Support\Collection
     */
    public function expeditionsHasRelations($relations, array $attributes = ['*']);

    /**
     * Find all expeditions having relation with whereIn clause.
     *
     * @param $relation
     * @param $attributeValues
     * @param array $attributes
     * @return mixed
     */
    public function expeditionsHasRelationWhereIn($relation, $attributeValues, array $attributes = ['*']);

    /**
     * Find Expedition with relations.
     *
     * @param integer $id
     * @param array|string $relations
     * @return mixed
     */
    public function expeditionFindWith($id, $relations);

    /**
     * Get count of Expedition Subjects.
     *
     * @param $id
     * @return mixed
     */
    public function getExpeditionSubjectCounts($id);

    /**
     * Get Expeditions Visible to user.
     *
     * @param $userId
     * @param array $relations
     * @return \Illuminate\Support\Collection|mixed
     */
    public function expeditionsByUserId($userId, array $relations =[]);
}
