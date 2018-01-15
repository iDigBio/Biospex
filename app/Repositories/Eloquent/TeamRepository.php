<?php

namespace App\Repositories\Eloquent;

use App\Models\Team as Model;
use App\Repositories\Interfaces\Team;

class TeamRepository extends EloquentRepository implements Team
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
}
