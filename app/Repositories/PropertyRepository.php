<?php 

namespace App\Repositories;

use App\Models\Property as Model;
use App\Interfaces\Property;

class PropertyRepository extends EloquentRepository implements Property
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
