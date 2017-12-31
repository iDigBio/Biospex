<?php

namespace App\Repositories;

use App\Models\AmChart as Model;
use App\Interfaces\AmChart;

class AmChartRepository extends EloquentRepository implements AmChart
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