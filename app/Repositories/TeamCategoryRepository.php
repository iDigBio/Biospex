<?php

namespace App\Repositories;

use App\Models\TeamCategory as Model;
use App\Interfaces\TeamCategory;

class TeamCategoryRepository extends EloquentRepository implements TeamCategory
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getTeamIndexPage()
    {
        return $this->model
            ->with('teams')
            ->orderBy('id', 'asc')
            ->groupBy('id')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getTeamCategorySelect()
    {
        return $this->model->pluck('name', 'id')->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getCategoriesWithTeams()
    {
        return $this->model->with('teams')->groupBy('id')->get();
    }
}
