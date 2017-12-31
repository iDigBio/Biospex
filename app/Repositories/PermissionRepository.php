<?php

namespace App\Repositories;

use App\Models\Permission as Model;
use App\Interfaces\Permission;

class PermissionRepository extends EloquentRepository implements Permission
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
