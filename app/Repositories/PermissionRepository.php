<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Permission;
use Biospex\Models\Permission as Model;

class PermissionRepository extends Repository implements Permission
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
