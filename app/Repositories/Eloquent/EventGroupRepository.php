<?php

namespace App\Repositories\Eloquent;

use App\Models\EventGroup as Model;
use App\Repositories\Interfaces\EventGroup;

class EventGroupRepository extends EloquentRepository implements EventGroup
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