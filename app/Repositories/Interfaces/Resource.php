<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Resource extends RepositoryInterface
{
    /**
     * Get resources ordered by id.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResourcesOrdered();

    /**
     * get trashed resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrashedResourcesOrdered();
}