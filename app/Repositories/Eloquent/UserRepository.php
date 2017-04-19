<?php

namespace App\Repositories\Eloquent;


use App\Models\User;
use App\Repositories\Contracts\UserContract;
use Illuminate\Contracts\Container\Container;

class UserRepository extends EloquentRepository implements UserContract
{
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

    /**
     * @inheritdoc
     */
    public function findWithRelations($id, $relations)
    {
        return $this->with($relations)->find($id);
    }

}