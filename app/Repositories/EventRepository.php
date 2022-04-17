<?php
/*
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

namespace App\Repositories;

use App\Models\Event;
use App\Models\EventTeam;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function collect;

/**
 * Class EventRepository
 *
 * @package App\Repositories
 */
class EventRepository extends BaseRepository
{

    /**
     * EventRepository constructor.
     *
     * @param \App\Models\Event $event
     */
    public function __construct(Event $event)
    {

        $this->model = $event;
    }

    /**
     * Get events for admin index.
     *
     * @param \App\Models\User $user
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Support\Collection
     */
    public function getEventAdminIndex(User $user, $sort = null, $order = null): Collection
    {
        $results = $user->isAdmin() ?
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $results = $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('owner_id', $user->id)->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * Get events for public index.
     *
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return \Illuminate\Support\Collection
     */
    public function getEventPublicIndex($sort = null, $order = null, $projectId = null): Collection
    {
        $results = $projectId === null ? $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('project_id', $projectId)->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * Get event for show page.
     *
     * @param $eventId
     * @return mixed
     */
    public function getEventShow($eventId)
    {
        return $this->model->withCount('transcriptions')->with([
            'project.lastPanoptesProject',
            'teams.users' => function ($q) use ($eventId) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($eventId) {
                        $q->where('event_id', $eventId);
                    },
                ]);
            },
        ])->find($eventId);
    }

    /**
     * Created event.
     *
     * @param array $attributes
     * @return mixed
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
     * Update event.
     *
     * @param array $attributes
     * @param $resourceId
     * @return false
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
     * Get event scoreboard.
     *
     * @param $eventId
     * @param array|string[] $columns
     * @return mixed
     */
    public function getEventScoreboard($eventId, array $columns = ['*'])
    {
        return $this->model->withCount('transcriptions')->with([
            'teams' => function ($q) use ($eventId) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($eventId) {
                        $q->where('event_id', $eventId);
                    },
                ])->orderBy('transcriptions_count', 'desc');
            },
        ])->find($eventId, $columns);
    }

    /**
     * Get events by project id.
     *
     * @param $projectId
     * @return \Illuminate\Support\Collection
     */
    public function getEventsByProjectId($projectId): Collection
    {
        return $this->model->withCount('transcriptions')->with([
            'teams' => function ($q) {
                $q->withCount('transcriptions')->orderBy('transcriptions_count', 'desc');
            },
        ])->whereHas('teams')->where('project_id', $projectId)->get();
    }

    /**
     * Check if classifications exist for event.
     *
     * @param int $projectId
     * @param int $userId
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function checkEventExistsForClassificationUserByDate(int $projectId, int $userId, string $date)
    {
        $callback = function ($q) use($userId){
            $q->where('user_id', $userId);
        };

        return $this->model->with(['teams' => function($q) use($callback) {
            $q->whereHas('users', $callback);
            $q->with(['users' => $callback]);
        }])
            ->where('project_id', $projectId)
            ->where('start_date', '<', $date)
            ->where('end_date', '>', $date)->get();
    }

    /**
     * Handle team create, update, delete.
     *
     * @param $team
     * @param $event
     */
    protected function handleTeam($team, $event)
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

    }

    /**
     * Sort results for index pages.
     *
     * @param $order
     * @param $results
     * @param $sort
     * @return \Illuminate\Support\Collection
     */
    protected function sortResults($order, $results, $sort): Collection
    {
        if ($order === null) {
            return $results;
        }

        switch ($sort) {
            case 'title':
                $results = $order === 'desc' ? $results->sortByDesc('title') : $results->sortBy('title');
                break;
            case 'project':
                $results = $order === 'desc' ? $results->sortByDesc(function ($event) {
                    return $event->project->title;
                }) : $results->sortBy(function ($event) {
                    return $event->project->title;
                });
                break;
            case 'date':
                $results = $order === 'desc' ? $results->sortByDesc('start_date') : $results->sortBy('start_date');
                break;
        }

        return $results;
    }
}