<?php namespace App\Repositories;

use App\Repositories\Contracts\Permission;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class PermissionRepository extends Repository implements Permission, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Permission::class;
    }
}
