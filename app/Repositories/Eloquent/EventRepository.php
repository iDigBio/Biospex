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

use App\Models\Event as Model;
use App\Models\EventTeam;
use App\Models\User;
use App\Repositories\Interfaces\Event;
use Illuminate\Support\Carbon;

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
    public function getEventPublicIndex($sort = null, $order = null, $projectId = null)
    {
        $results = $projectId === null ? $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('project_id', $projectId)->get();

        $this->resetModel();

        if($order === null) {
            return $results;
        }

        switch ($sort) {
            case 'title':
                return $order === 'desc' ? $results->sortByDesc('title') :
                    $results->sortBy('title');
            case 'project':
                return $order === 'desc' ?
                    $results->sortByDesc(function ($event) { return $event->project->title; }) :
                    $results->sortBy(function ($event) { return $event->project->title; });
            case 'date':
                return $order === 'desc' ? $results->sortByDesc('start_date') :
                    $results->sortBy('start_date');
        }
    }

    /**
     * @inheritdoc
     */
    public function getEventAdminIndex(User $user, $sort = null, $order = null)
    {
        $results = $user->isAdmin() ? $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $results = $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('owner_id', $user->id)->get();

        $this->resetModel();

        if($order === null) {
            return $results;
        }

        switch ($sort) {
            case 'title':
                return $order === 'desc' ? $results->sortByDesc('title') :
                    $results->sortBy('title');
            case 'project':
                return $order === 'desc' ?
                    $results->sortByDesc(function ($event) { return $event->project->title; }) :
                    $results->sortBy(function ($event) { return $event->project->title; });
            case 'date':
                return $order === 'desc' ? $results->sortByDesc('start_date') :
                    $results->sortBy('start_date');
        }
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
            $this->handleTeam($team, $event);
        });

        return $event;
    }

    /**
     * Handle team create, update, delete.
     *
     * @param $team
     * @param $event
     */
    public function handleTeam($team, $event)
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
            'project.lastPanoptesProject',
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
     * Check if an event exists with team and user.
     *
     * @param $projectId
     * @param $user
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function checkEventExistsForClassificationUser($projectId, $user)
    {
        $events = $this->model->with(['teams' => function($q) use($user) {
            $q->whereHas('users', function($query) use ($user){
                $query->where('user_id', $user->id);
            });
        }])->whereHas('teams', function($q) use ($user, $projectId) {
            $q->whereHas('users', function($query) use ($user){
                $query->where('user_id', $user->id);
            });
        })->where('project_id', $projectId)->get();

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
                    $q->withCount('transcriptions')->orderBy('transcriptions_count', 'desc');
                },
            ])->whereHas('teams')->where('project_id', $projectId)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * Get event for scoreboard.
     *
     * @param $eventId
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function getEventScoreboard($eventId, array $columns = ['*'])
    {
        $results = $this->model->withCount('transcriptions')->with([
            'teams' => function ($q) use ($eventId) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($eventId) {
                        $q->where('event_id', $eventId);
                    },
                ])->orderBy('transcriptions_count', 'desc');
            },
        ])->find($eventId, $columns);

        $this->resetModel();

        return $results;
    }
}