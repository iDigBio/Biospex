<?php

namespace App\Repositories\Eloquent;

use App\Models\State as Model;
use App\Repositories\Interfaces\State;

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
        $results = $this->model->truncate();

        $this->resetModel();

        return $results;
    }
}