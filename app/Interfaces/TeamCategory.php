<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface TeamCategory extends Eloquent
{

    /**
     * @return Collection
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