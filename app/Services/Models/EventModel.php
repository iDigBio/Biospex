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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Models\Event;
use App\Models\EventTeam;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EventModel extends ModelService
{
    private EventTeamModel $eventTeamModel;

    /**
     * EventModel constructor.
     */
    public function __construct(Event $model, EventTeamModel $eventTeamModel)
    {
        $this->model = $model;
        $this->eventTeamModel = $eventTeamModel;
    }

    /**
     * Get events for admin index.
     *
     * @param  null  $sort
     * @param  null  $order
     */
    public function getAdminIndex(User $user, $sort = null, $order = null): Collection
    {
        $results = $user->isAdmin() ?
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('owner_id', $user->id)->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * Get events for public index.
     *
     * @param  null  $sort
     * @param  null  $order
     * @param  null  $projectId
     */
    public function getPublicIndex($sort = null, $order = null, $projectId = null): Collection
    {
        $results = $projectId === null ?
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->model->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('project_id', $projectId)->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * Get event for show page.
     */
    public function getShow($eventId): mixed
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
     * Overwrite model update method.
     */
    public function update(array $data, $resourceId): false
    {
        $this->setDates($data);

        $event = $this->model->find($resourceId);
        $event = $event->fill($data)->save();

        collect($data['teams'])->each(function ($team) use ($event) {
            $this->handleTeam($team, $event);
        });

        return $event;
    }

    /**
     * Sort results for index pages.
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

    /**
     * Handle team create, update, delete.
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
     * Get event scoreboard.
     *
     * @param  array|string[]  $columns
     */
    public function getEventScoreboard($eventId, array $columns = ['*']): mixed
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
     * Get any ongoing events for user using project id and dates.
     */
    public function getAnyEventsForUserByProjectIdAndDate(int $projectId, int $userId, string $date): \Illuminate\Database\Eloquent\Collection|array
    {
        $callback = function ($q) use ($userId) {
            $q->where('user_id', $userId);
        };

        return $this->model->with(['teams' => function ($q) use ($callback) {
            $q->whereHas('users', $callback);
            $q->with(['users' => $callback]);
        }])
            ->where('project_id', $projectId)
            ->where('start_date', '<', $date)
            ->where('end_date', '>', $date)->get();
    }

    /**
     * Set dates for event.
     */
    public function setDates(array $data): array
    {
        $data['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['start_date'].':00', $data['timezone']);
        $data['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['end_date'].':00', $data['timezone']);

        return $data;
    }
}