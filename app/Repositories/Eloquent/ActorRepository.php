<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories\Eloquent;

use App\Models\Actor as Model;
use App\Models\ActorContact;
use App\Repositories\Interfaces\Actor;

class ActorRepository extends EloquentRepository implements Actor
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function createActor(array $attributes = [])
    {
        $actor = $this->model->create($attributes);

        foreach ($attributes['contacts'] as $contact)
        {
            if ($contact['email'] !== '')
            {
                $actor->contacts()->create(['email' => $contact['email']]);
            }
        }

        $this->resetModel();

        return $actor;
    }

    /**
     * @inheritdoc
     */
    public function updateActor(array $attributes = [], $actorId)
    {
        $actor = $this->model->with(['contacts'])->find($actorId);
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

        $actor = $this->model->with(['contacts'])->find($actorId);

        $this->resetModel();

        return $actor;
    }

    /**
     * @inheritdoc
     */
    public function updateActorExpeditionPivot($actor, $expeditionId, array $attributes = [])
    {
        $result = $actor->expeditions()->updateExistingPivot($expeditionId, $attributes);

        $this->resetModel();

        return $result;
    }
}