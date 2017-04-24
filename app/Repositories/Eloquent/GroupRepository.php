<?php

namespace App\Repositories\Eloquent;


use App\Models\Group;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

class GroupRepository extends EloquentRepository implements GroupContract
{
    use EloquentRepositoryCommon;

    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Group::class)
            ->setRepositoryId('biospex.repository.group');
    }
}