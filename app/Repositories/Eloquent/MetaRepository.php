<?php 

namespace App\Repositories\Eloquent;

use App\Models\Meta as Model;
use App\Repositories\Interfaces\Meta;

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
