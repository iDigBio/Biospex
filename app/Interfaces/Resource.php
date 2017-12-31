<?php

namespace App\Interfaces;


interface Resource extends Eloquent
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