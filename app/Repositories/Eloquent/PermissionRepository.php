<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionContract;
use Illuminate\Contracts\Container\Container;

class PermissionRepository extends EloquentRepository implements PermissionContract
{

    /**
     * PermissionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Permission::class)
            ->setRepositoryId('biospex.repository.permission');
    }
}
