<?php

namespace App\Repositories\Contracts;

interface ProjectContract extends RepositoryContract, CacheableContract
{

    /**
     * Get Project with relationships.
     *
     * @param $projectId
     * @param array $relations
     * @param array $attributes
     * @return mixed
     */
    public function projectFindWith($projectId, array $relations = [], array $attributes = ['*']);

    /**
     * Get random projects for carousel.
     *
     * @param int $count
     * @return mixed
     */
    public function getRandomProjectsForCarousel($count = 5);
}