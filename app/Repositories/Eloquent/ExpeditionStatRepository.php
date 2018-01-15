<?php 

namespace App\Repositories\Eloquent;

use App\Models\ExpeditionStat as Model;
use App\Repositories\Interfaces\ExpeditionStat;

class ExpeditionStatRepository extends EloquentRepository implements ExpeditionStat
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

