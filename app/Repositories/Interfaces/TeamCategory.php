<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface TeamCategory extends RepositoryInterface
{

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTeamIndexPage();

    /**
     * @return mixed
     */
    public function getTeamCategorySelect();

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategoriesWithTeams();
}