<?php

namespace App\Repositories\Eloquent;


use App\Models\User;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

class UserRepository extends EloquentRepository implements UserContract
{
    use EloquentRepositoryCommon;

    /**
     * ExpeditionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(User::class)
            ->setRepositoryId('biospex.repository.user');

    }

}