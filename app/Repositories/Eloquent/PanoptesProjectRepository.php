<?php

namespace App\Repositories\Eloquent;

use App\Models\PanoptesProject as Model;
use App\Repositories\Interfaces\PanoptesProject;

class PanoptesProjectRepository extends EloquentRepository implements PanoptesProject
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