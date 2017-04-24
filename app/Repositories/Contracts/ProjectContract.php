<?php

namespace App\Repositories\Contracts;

interface ProjectContract extends RepositoryContract, CacheableContract
{
    /**
     * Get random projects for carousel.
     *
     * @param int $count
     * @return mixed
     */
    public function getRandomProjectsForCarousel($count = 5);
}