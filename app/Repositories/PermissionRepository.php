<?php namespace App\Repositories;

use App\Repositories\Contracts\Permission;
use App\Models\Permission as Model;

class PermissionRepository extends Repository implements Permission
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // Methods contained implemented in interface must exist here
    public function getPermissionsGroupBy()
    {
        return $this->model->getPermissionsGroupBy();
    }

    public function setPermissions(array $data)
    {
        return $this->model->setPermissions($data);
    }
}
