<?php

namespace App\Repositories\Eloquent;

use App\Models\ProjectResource as Model;
use App\Repositories\Interfaces\ProjectResource;

class ProjectResourceRepository extends EloquentRepository implements ProjectResource
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

