<?php

namespace App\Repositories;

use App\Models\ActorContact;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ActorRepository extends Repository implements Actor, CacheableInterface
{

    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Actor::class;
    }

    /**
     * Override parent create to allow sync.
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $actor = $this->model->create($attributes);

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
     * Override update method.
     *
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $actor = $this->model->with(['contacts'])->find($id);
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
}
