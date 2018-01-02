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
        $results = $this->model
            ->with('teams')
            ->orderBy('id', 'asc')
            ->groupBy('id')
            ->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getTeamCategorySelect()
    {
        $results = $this->model->pluck('name', 'id')->toArray();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getCategoriesWithTeams()
    {
        $results = $this->model->with('teams')->groupBy('id')->get();

        $this->resetModel();

        return $results;
    }
}
