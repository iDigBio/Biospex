<?php

namespace App\Repositories\Contracts;

interface CriterionContract
{
    /**
     * Apply current criterion to the given query and return query.
     *
     * @param mixed              $query
     * @param RepositoryContract $repository
     *
     * @return mixed
     */
    public function apply($query, $repository);
}
