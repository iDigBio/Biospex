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

namespace App\Services;

use App\Models\Event;
use App\Models\EventTeam;
use App\Models\User;
use App\Services\Helpers\DateService;
use Illuminate\Support\Collection;

class EventService
{
    public function __construct(protected Event $event, protected EventTeam $eventTeam, protected DateService $dateService) {}

    /**
     * Get events for admin index.
     */
    public function getAdminIndex(User $user, array $request = []): Collection
    {
        $records = $user->isAdmin() ?
            $this->event->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->event->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('owner_id', $user->id)->get();

        $sortedRecords = $this->sortRecords($records, $request);

        return $this->partitionRecords($sortedRecords);
    }

    /**
     * Sort results for index pages.
     */
    protected function sortRecords(Collection $records, array $request = []): Collection
    {
        if (! isset($request['order'])) {
            return $records;
        }

        return match ($request['sort']) {
            'title' => $request['order'] === 'desc' ? $records->sortByDesc('title') : $records->sortBy('title'),
            'project' => $request['order'] === 'desc' ? $records->sortByDesc(function ($event) {
                return $event->project->title;
            }) : $records->sortBy(function ($event) {
                return $event->project->title;
            }),
            'date' => $request['order'] === 'desc' ? $records->sortByDesc('start_date') : $records->sortBy('start_date'),
            default => $records,
        };
    }

    /**
     * Partition records into incomplete and complete.
     */
    protected function partitionRecords(Collection $records): Collection
    {
        return $records->partition(function ($event) {
            return $this->dateService->eventBefore($event) || $this->dateService->eventActive($event);
        });
    }

    /**
     * Get event for show page.
     */
    public function getAdminShow(Event &$event): void
    {
        $event->loadCount('transcriptions')->load([
            'project:id,title,slug,logo_file_name',
            'project.lastPanoptesProject:id,project_id,panoptes_project_id,panoptes_workflow_id',
            'teams:id,uuid,event_id,title', 'teams.users' => function ($q) use ($event) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    },
                ]);
            },
        ]);
    }

    /**
     * Get event for show page.
     */
    public function edit(Event &$event): void
    {
        $event->loadCount('transcriptions')->loadCount('teams')->load('teams:id,uuid,event_id,title');
    }

    /**
     * Create event.
     */
    public function store(array $attributes): Event
    {
        $this->setEventDates($attributes);
        $event = $this->event->create($attributes);
        $event->teams()->saveMany($this->makeTeams($attributes['teams']));

        return $event;
    }

    /**
     * Overwrite model update method.
     */
    public function update(array $attributes, int $resourceId): bool
    {
        $this->setEventDates($attributes);

        $event = $this->event->find($resourceId);
        $result = $event->fill($attributes)->save();

        collect($attributes['teams'])->each(function ($team) use ($event) {
            $this->updateTeam($team, $event);
        });

        return $result;
    }

    /**
     * Set dates for event.
     */
    public function setEventDates(array &$data): void
    {
        $this->dateService->setEventDates($data);
    }

    /**
     * Make teams.
     */
    public function makeTeams(array $teams)
    {
        return collect($teams)->map(function ($team) {
            return $this->eventTeam->make($team);
        });
    }

    /**
     * Handle team updates from event.
     */
    protected function updateTeam(array $team, Event $event): void
    {
        $record = $this->eventTeam->where('id', $team['id'])->where('event_id', $event->id)->first();
        if ($record && $team['title'] !== null) {
            $record->fill($team)->save();

            return;
        }

        if ($record && $team['title'] === null) {
            $record->delete();

            return;
        }

        if (! $record && $team['title'] !== null) {
            $team = $this->eventTeam->make($team);
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
        return $this->event->withCount('transcriptions')->with([
            'teams' => function ($q) use ($eventId) {
                $q->withcount([
                    'transcriptions' => function ($q) use ($eventId) {
                        $q->where('event_id', $eventId);
                    },
                ])->orderBy('transcriptions_count', 'desc');
            },
        ])->find($eventId, $columns);
    }
}
