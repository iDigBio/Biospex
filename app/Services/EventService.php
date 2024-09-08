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
    public function index(User $user, ?string $sort = null, ?string $order = null): Collection
    {
        $records = $user->isAdmin() ?
            $this->event->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->get() :
            $this->event->with(['project.lastPanoptesProject', 'teams:id,title,event_id'])->where('owner_id', $user->id)->get();

        $sortedRecords = $this->sortRecords($records, $sort, $order);

        return $this->partitionRecords($sortedRecords);
    }

    /**
     * Sort results for index pages.
     */
    protected function sortRecords(Collection $records, ?string $sort = null, ?string $order = null): Collection
    {
        if ($order === null) {
            return $records;
        }

        return match ($sort) {
            'title' => $order === 'desc' ? $records->sortByDesc('title') : $records->sortBy('title'),
            'project' => $order === 'desc' ? $records->sortByDesc(function ($event) {
                return $event->project->title;
            }) : $records->sortBy(function ($event) {
                return $event->project->title;
            }),
            'date' => $order === 'desc' ? $records->sortByDesc('start_date') : $records->sortBy('start_date'),
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
}
