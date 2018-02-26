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
    public function createEvent(array $attributes)
    {
        $event = $this->create($attributes);

        $groups = collect($attributes['groups'])->reject(function ($group) {
            return empty($group['title']);
        })->map(function ($group) {
            return new EventGroup($group);
        });

        $event->groups()->saveMany($groups->all());

        return $event;
    }

    /**
     * Update event and groups.
     *
     * @param array $attributes
     * @param $resourceId
     * @return bool
     * @throws \Exception
     */
    public function updateEvent(array $attributes, $resourceId)
    {
        $event = $this->update($attributes, $resourceId);

        collect($attributes['groups'])->each(function ($group) use ($event) {
            $this->handleGroup($group, $event);
        });

        return $event;
    }

    public function handleGroup($group, $event)
    {
        $record = EventGroup::where('id', $group['id'])->where('event_id', $event->id)->first();
        if ($record && $group['title'] !== null) {
            $record->fill($group)->save();

            return;
        }

        if ($record && $group['title'] === null) {
            $record->delete();

            return;
        }

        if (!$record && $group['title'] !== null) {
            $group = new EventGroup($group);
            $event->groups()->save($group);
        }

        return;
    }

    /**
     * Get events created by user.
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|mixed|static[]
     * @throws \Exception
     */
    public function getUserEvents($id)
    {
       $results = $this->model->withCount(['transcriptions' => function($query) {
           $query->groupBy('event_id');
       }])->where('owner_id', $id)->get();

       $this->resetModel();

       return $results;
    }

    /**
     * Get records for show event page.
     *
     * @param $eventId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|mixed|static[]
     * @throws \Exception
     */
    public function getEventShow($eventId)
    {
        $results = $this->model->with(['groups.users.transcriptionCount'])->find($eventId);

        $this->resetModel();

        return $results;
    }
}