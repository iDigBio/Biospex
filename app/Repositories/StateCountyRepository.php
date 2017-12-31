<?php

namespace App\Repositories;

use App\Models\State as Model;
use App\Interfaces\State;

class StateCountyRepository extends EloquentRepository implements State
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
    public function truncateTable()
    {
        return $this->model->truncate();
    }
}