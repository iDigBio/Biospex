<?php

namespace App\Repositories\Eloquent;

use App\Models\Event as Model;
use App\Models\EventTeam;
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
        $attributes['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['start_date'].':00', $attributes['timezone']);
        $attributes['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['end_date'].':00', $attributes['timezone']);

        $event = $this->create($attributes);

        $teams = collect($attributes['teams'])->reject(function ($team) {
            return empty($team['title']);
        })->map(function ($team) {
            return new EventTeam($team);
        });

        $event->teams()->saveMany($teams->all());

        return $event;
    }

    /**
     * Update event and teams.
     *
     * @param array $attributes
     * @param $resourceId
     * @return bool
     * @throws \Exception
     */
    public function updateEvent(array $attributes, $resourceId)
    {
        $attributes['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['start_date'].':00', $attributes['timezone']);
        $attributes['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $attributes['end_date'].':00', $attributes['timezone']);

        $event = $this->update($attributes, $resourceId);

        collect($attributes['teams'])->each(function ($team) use ($event) {
            $this->handleGroup($team, $event);
        });

        return $event;
    }

    public function handleGroup($team, $event)
    {
        $record = EventTeam::where('id', $team['id'])->where('event_id', $event->id)->first();
        if ($record && $team['title'] !== null) {
            $record->fill($team)->save();

            return;
        }

        if ($record && $team['title'] === null) {
            $record->delete();

            return;
        }

        if (! $record && $team['title'] !== null) {
            $team = new EventTeam($team);
            $event->teams()->save($team);
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
        $results = $this->model->with('project')->withCount([
            'transcriptions' => function ($query) {
                $query->groupBy('event_id');
            },
        ])->where('owner_id', $id)->get();

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
        $results = $this->model->withCount('transcriptions')->with([
            'project',
            'teams.users' => function ($q) use ($eventId) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($eventId) {
                        $q->where('event_id', $eventId);
                    },
                ]);
            },
        ])->find($eventId);

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
     * Check if an event exists with team and user.
     *
     * @param $projectId
     * @param $user
     * @return \Illuminate\Database\Eloquent\Model|mixed|null|object|static
     * @throws \Exception
     */
    public function checkEventExistsForClassificationUser($projectId, $user)
    {
        $events = $this->model->with([
            'teams' => function ($q) use ($user) {
                $q->with(['users'])->whereHas('users', function ($q) use ($user) {
                    $q->where('nfn_user', $user);
                });
            },
        ])->where('project_id', $projectId)->get();

        $this->resetModel();

        return $events;
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
        $results = $this->model->withCount('transcriptions')->with([
                'teams' => function ($q) {
                    $q->withCount('transcriptions');
                },
            ])->whereHas('teams')->where('project_id', $projectId)->get();

        $this->resetModel();

        return $results;
    }
}