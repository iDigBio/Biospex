<?php

namespace App\Repositories\Contracts;

interface ProjectContract extends RepositoryContract, CacheableContract
{

    /**
     * Get random projects for carousel.
     *
     * @param int $count
     * @param array $attributes
     * @return mixed
     */
    public function getRandomProjectsForCarousel($count = 5, array $attributes = ['*']);

    /**
     * Get recent projects for The Projects widget.
     *
     * @param int $count
     * @param array $attributes
     * @return mixed
     */
    public function getRecentProjects($count = 5, array $attributes = ['*']);
}