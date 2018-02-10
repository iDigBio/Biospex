<?php

namespace App\Repositories\Eloquent;

use App\Models\Event as Model;
use App\Models\EventGroup;
use App\Repositories\Interfaces\Event;

class EventRepository extends EloquentRepository implements Event
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
    public function create(array $attributes)
    {
        $event = $this->model->create($attributes);

        $groups = collect($attributes['groups'])->reject(function ($group) {
            return empty($group['title']);
        })->map(function ($group) {
            return new EventGroup($group);
        });

        $event->groups()->saveMany($groups->all());

        return $event;
    }

    public function filterOrDeleteResources($resource)
    {
        if ($resource['type'] === null)
        {
            return true;
        }

        if ($resource['type'] === 'delete')
        {
            $this->projectResource->delete($resource['id']);

            return true;
        }

        return false;
    }
}