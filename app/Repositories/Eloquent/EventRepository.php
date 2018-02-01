<?php

namespace App\Repositories\Eloquent;

use App\Models\Event as Model;
use App\Repositories\Interfaces\Event;

class EventRepository extends EloquentRepository implements Event
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