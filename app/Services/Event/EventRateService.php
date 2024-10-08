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

namespace App\Services\Event;

use App\Models\Event;
use App\Services\Helpers\DateService;
use App\Services\Traits\RateChartTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class EventRateService
 */
readonly class EventRateService
{
    use RateChartTrait;

    /**
     * AjaxService constructor.
     */
    public function __construct(
        protected EventTranscriptionService $eventTranscriptionService,
        protected DateService $dateService,
        protected Carbon $carbon
    ) {}

    /**
     * Get event transcription data for step chart.
     */
    public function eventStepChart(Event $event, ?string $timestamp = null): ?array
    {
        $event->load('teams');

        $loadTime = $this->getLoadTime($event, $this->carbon, $timestamp);

        $startLoad = $loadTime->copy();

        $endLoad = $this->getEndLoad($event, $loadTime, $timestamp);

        $intervals = $this->setTimeIntervals($startLoad, $endLoad, $timestamp);

        $transcriptions = $this->eventTranscriptionService->getEventRateChartTranscriptions($event->id, $startLoad, $endLoad);

        return $transcriptions->isEmpty() ? $this->processEmptyResult($event, $intervals) : $this->processTranscriptionResult($event, $transcriptions, $intervals);
    }

    /**
     * Process an empty result set as this means it's very beginning.
     */
    public function processEmptyResult(Event $event, Collection $intervals): array
    {
        if ($this->dateService->eventAfter($event)) {
            return [];
        }

        $teams = $event->teams->pluck('title');

        $data = [];
        $intervals->each(function ($interval, $key) use (&$data, $teams) {
            $teams->each(function ($team) use (&$data, $key) {
                $data[$key][$team] = 0;
            });
        });

        return $data;
    }

    /**
     * Process query data for results.
     */
    protected function processTranscriptionResult(
        Event $event,
        Collection $transcriptions,
        Collection $intervals
    ): array {
        $mapped = $this->mapWithDateKeys($transcriptions, $event);

        $merged = $this->mergeIntervals($mapped, $intervals);

        $transformed = $this->addMissingTeamCount($event, $merged);

        return $this->setDateInArray($transformed);
    }

    /**
     * Map teams and data using keys.
     * Count is per hour (count * 12) for each 5 minutes.
     */
    protected function mapWithDateKeys(Collection $transcriptions, Event $event): Collection
    {
        $data = [];
        $transcriptions->each(function ($transcription) use (&$data, $event) {
            $date = $this->carbon::parse($transcription->time)->timezone($event->timezone)->format('Y-m-d H:i:s');
            $data[$date][$transcription->team->title] = $transcription->count * 12;
        });

        return collect($data);
    }

    /**
     * Merge mapped and intervals.
     */
    protected function mergeIntervals(Collection $mapped, Collection $intervals): Collection
    {
        return $intervals->merge($mapped)->map(function ($collection) {
            return is_array($collection) ? $collection : [];
        });
    }

    /**
     * Add missing team counts. Set to 0.
     */
    protected function addMissingTeamCount(Event $event, Collection $merged): Collection
    {
        $teams = collect($event->teams->pluck('title'));

        return $merged->transform(function ($collection, $key) use ($teams) {
            $teams->each(function ($team) use (&$collection) {
                if (! isset($collection[$team])) {
                    $collection[$team] = 0;
                }
            });

            return $collection;
        });
    }

    /**
     * Get end load time. If event is over, it will display all points from beginning to end.
     */
    protected function getEndLoad(Event $event, Carbon $loadTime, ?string $timestamp = null): Carbon
    {
        if ($this->dateService->eventAfter($event)) {
            return $event->end_date;
        }

        return $timestamp === null ?
            $this->carbon::now()->floorMinutes(5) : $loadTime->addMinutes(5);
    }
}
