<?php 

namespace App\Repositories;

use App\Models\ActorContact as Model;
use App\Interfaces\ActorContact;

class ActorContactRepository extends EloquentRepository implements ActorContact
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    function model()
    {
        return Model::class;
    }
}
