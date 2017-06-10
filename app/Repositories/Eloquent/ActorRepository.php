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
    public function getAllTrashed()
    {
        return $this->onlyTrashed()->get();
    }

    /**
     * @inheritdoc
     */
    public function createActor(array $attributes = [])
    {
        $actor = $this->create($attributes);

        foreach ($attributes['contacts'] as $contact)
        {
            if ($contact['email'] !== '')
            {
                $actor->contacts()->create(['email' => $contact['email']]);
            }
        }

        return $actor;
    }

    /**
     * @inheritdoc
     */
    public function updateActor($id, array $attributes = [])
    {
        $actor = $this->with(['contacts'])->find($id);
        $actor->fill($attributes);
        $actor->save();

        $contacts = [];
        $actor->contacts()->delete();
        foreach ($attributes['contacts'] as $contact)
        {
            if ($contact['email'] !== '')
            {
                $contacts[] = new ActorContact(['email' => $contact['email']]);
            }
        }

        $actor->contacts()->saveMany($contacts);

        $actor = $this->model->with(['contacts'])->find($id);

        return $actor;
    }

    /**
     * @inheritdoc
     */
    public function updateActorExpeditionPivot($actor, $expeditionId, array $attributes = [])
    {
        return $actor->expeditions()->updateExistingPivot($expeditionId, $attributes);
    }
}