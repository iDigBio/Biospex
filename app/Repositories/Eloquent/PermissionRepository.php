<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission as Model;
use App\Repositories\Interfaces\Permission;

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
