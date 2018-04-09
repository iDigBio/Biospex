<?php

namespace App\Repositories\Eloquent;

use App\Models\EventUser as Model;
use App\Repositories\Interfaces\EventUser;

class EventUserRepository extends EloquentRepository implements EventUser
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