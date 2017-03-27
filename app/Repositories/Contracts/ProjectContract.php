<?php

namespace App\Repositories\Contracts;

interface ProjectContract extends RepositoryContract, CacheableContract
{

    /**
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findAllHasRelationsWithRelations(array $hasRelations = [], array $relations = [], array $attributes = ['*']);

    /**
     * Get Project with relationships.
     *
     * @param $projectId
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findWithRelations($projectId, array $relations = [], array $attributes = ['*']);

    /**
     * @param $attributeValues
     * @param array $hasRelations
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function findWhereInHasRelationsWithRelations($attributeValues, array $hasRelations = [], array $relations = [], array $attributes = ['*']);

    /**
     * Get random projects for carousel.
     *
     * @param int $count
     * @return mixed
     */
    public function getRandomProjectsForCarousel($count = 5);
}