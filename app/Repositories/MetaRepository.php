<?php 

namespace App\Repositories;

use App\Models\Meta as Model;
use App\Interfaces\Meta;

class MetaRepository extends EloquentRepository implements Meta
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
