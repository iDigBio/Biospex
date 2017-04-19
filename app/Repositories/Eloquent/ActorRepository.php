<?php

namespace App\Repositories\Eloquent;

use App\Models\Actor;
use App\Repositories\Contracts\ActorContract;
use Illuminate\Contracts\Container\Container;

class ActorRepository extends EloquentRepository implements ActorContract
{

    /**
     * ActorRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Actor::class)
            ->setRepositoryId('biospex.repository.actor');
    }

    /**
     * @inheritdoc
     */
    public function findWithRelations($id, array $relations = [])
    {
        return $this->with($relations)->find($id);
    }

    /**
     * @inheritdoc
     */
    public function getAllTrashed()
    {
        return $this->onlyTrashed()->get();
    }

    /**
     * @inheritdoc
     */
    public function createActor(array $attributes = [])
    {
        return $this->create($attributes);
    }
}