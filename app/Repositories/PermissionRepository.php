<?php namespace App\Repositories;

use App\Repositories\Contracts\Permission;
use App\Models\Permission as Model;

class PermissionRepository extends Repository implements Permission
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
