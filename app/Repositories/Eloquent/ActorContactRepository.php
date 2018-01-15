<?php 

namespace App\Repositories\Eloquent;

use App\Models\ActorContact as Model;
use App\Repositories\Interfaces\ActorContact;

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
