<?php

namespace App\Repositories\Contracts;

interface ExpeditionContract extends RepositoryContract, CacheableContract
{

    /**
     * Retrieve expeditions for Notes From Nature classification process.
     *
     * @param array $ids
     * @param array $attributes
     * @return mixed
     */
    public function getExpeditionsForNfnClassificationProcess(array $ids = [], array $attributes = ['*']);

    /**
     * Find all expeditions having relations and with relations.
     *
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findAllHasRelationsWithRelations(array $hasRelations = [], array $relations = [], array $attributes = ['*']);

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
     * Find with relations.
     *
     * @param integer $id
     * @param array|string $relations
     * @return mixed
     */
    public function findWithRelations($id, $relations);

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
