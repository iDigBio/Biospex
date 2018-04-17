<?php

namespace App\Repositories\Eloquent;

use App\Models\Event as Model;
use App\Models\EventGroup;
use App\Repositories\Interfaces\Event;
use Carbon\Carbon;

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
        $attributes['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['start_date'] . ':00', $attributes['timezone']);
        $attributes['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['end_date'] . ':00', $attributes['timezone']);

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
        $attributes['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['start_date'] . ':00', $attributes['timezone']);
        $attributes['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['end_date'] . ':00', $attributes['timezone']);

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
        $results = $this->model->with(['transcriptionCount', 'groups.users.transcriptionCount', 'project'])->find($eventId);

        $this->resetModel();

        return $results;
    }

    /**
     * Return transcriptions ids for event.
     *
     * @param $eventId
     * @return mixed
     * @throws \Exception
     */
    public function getEventClassificationIds($eventId)
    {
        $event = $this->model->with(['transcriptions'])->find($eventId);
        $ids = $event->transcriptions->pluck('classification_id');

        $this->resetModel();

        return $ids;
    }

    /**
     * Check if an event exists with group and user.
     *
     * @param $projectId
     * @param $user
     * @return \Illuminate\Database\Eloquent\Model|mixed|null|object|static
     * @throws \Exception
     */
    public function checkEventExistsForClassificationUser($projectId, $user)
    {
        $event = \DB::table('events')
            ->join('event_groups', 'events.id', '=', 'event_groups.event_id')
            ->join('event_group_user', 'event_groups.id', '=', 'event_group_user.group_id')
            ->join('event_users', 'event_group_user.user_id', '=', 'event_users.id')
            ->select('events.id as event_id', 'event_group_user.group_id', 'event_group_user.user_id')
            ->where('events.project_id', $projectId)
            ->where('event_users.nfn_user', $user)
            ->first();

        return $event;
    }

    /**
     * Get events using project id.
     *
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed|null|static|static[]
     * @throws \Exception
     */
    public function getEventsByProjectId($projectId)
    {
        // TODO add groups being returned by count. Desc.
        $results = $this->model->with(['groups'])->whereHas('groups')->where('project_id', $projectId)->get();

        $this->resetModel();

        return $results;
    }
}