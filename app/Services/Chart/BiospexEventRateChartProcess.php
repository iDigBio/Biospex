<?php declare(strict_types=1);
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

namespace App\Services\Chart;

use App\Facades\DateHelper;
use App\Models\Event;
use App\Repositories\EventRepository;
use App\Repositories\EventTranscriptionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function collect;

/**
 * Class BiospexEventRateChartProcess
 *
 * @package App\Services\Process
 */
class BiospexEventRateChartProcess
{
    /**
     * @var \App\Repositories\EventRepository
     */
    private EventRepository $eventRepo;

    /**
     * @var \App\Repositories\EventTranscriptionRepository
     */
    private EventTranscriptionRepository $eventTranscriptionRepo;

    /**
     * AjaxService constructor.
     *
     * @param \App\Repositories\EventRepository $eventRepo
     * @param \App\Repositories\EventTranscriptionRepository $eventTranscriptionRepo
     */
    public function __construct(
        EventRepository $eventRepo,
        EventTranscriptionRepository $eventTranscriptionRepo
    ) {
        $this->eventRepo = $eventRepo;
        $this->eventTranscriptionRepo = $eventTranscriptionRepo;
    }

    /**
     * Get event transcription data for step chart.
     *
     * @param string $eventId
     * @param string|null $timestamp
     * @return array
     */
    public function eventStepChart(string $eventId, string $timestamp = null): ?array
    {
        $event = $this->eventRepo->findWith($eventId, ['teams']);
        if ($event === null) {
            return null;
        }

        $loadTime = $this->getLoadTime($event, $timestamp);

        $startLoad = $loadTime->copy();

        $endLoad = $this->getEndLoad($event, $loadTime, $timestamp);

        $intervals = $this->setTimeIntervals($startLoad, $endLoad, $timestamp);

        $transcriptions = $this->eventTranscriptionRepo->getEventRateChartTranscriptions($eventId, $startLoad, $endLoad);

        return $transcriptions->isEmpty() ? $this->processEmptyResult($event, $intervals) : $this->processTranscriptionResult($event, $transcriptions, $intervals);
    }

    /**
     * Process an empty result set as this means it's very beginning.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    public function processEmptyResult(Event $event, Collection $intervals): array
    {
        if (DateHelper::eventAfter($event)) {
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
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $transcriptions
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    protected function processTranscriptionResult(
        Event $event,
        Collection $transcriptions,
        Collection $intervals
    ) {
        $mapped = $this->mapWithDateKeys($transcriptions, $event);

        $merged = $this->mergeIntervals($mapped, $intervals);

        $transformed = $this->addMissingTeamCount($event, $merged);

        return $this->setDateInArray($transformed);
    }

    /**
     * Map teams and data using keys.
     * Count is per hour (count * 12) for each 5 minutes.
     *
     * @param \Illuminate\Support\Collection $transcriptions
     * @param \App\Models\Event $event
     * @return \Illuminate\Support\Collection
     */
    protected function mapWithDateKeys(Collection $transcriptions, Event $event): Collection
    {
        $data = [];
        $transcriptions->each(function ($transcription) use (&$data, $event) {
            $date = Carbon::parse($transcription->time)->timezone($event->timezone)->format('Y-m-d H:i:s');
            $data[$date][$transcription->team->title] = $transcription->count * 12;
        });

        return collect($data);
    }

    /**
     * Merge mapped and intervals.
     *
     * @param \Illuminate\Support\Collection $mapped
     * @param \Illuminate\Support\Collection $intervals
     * @return \Illuminate\Support\Collection
     */
    protected function mergeIntervals(Collection $mapped, Collection $intervals): Collection
    {
        return $intervals->merge($mapped)->map(function ($collection) {
            return is_array($collection) ? $collection : [];
        });
    }

    /**
     * Add missing team counts. Set to 0.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Collection $merged
     * @return \Illuminate\Support\Collection
     */
    protected function addMissingTeamCount(Event $event, Collection $merged): Collection
    {
        $teams = collect($event->teams->pluck('title'));

        return $merged->transform(function ($collection, $key) use ($teams) {
            $teams->each(function ($team) use (&$collection, $key) {
                if (! isset($collection[$team])) {
                    $collection[$team] = 0;
                }
            });

            return $collection;
        });
    }

    /**
     * Set the date in the array and keys to numeric.
     *
     * @param \Illuminate\Support\Collection $transformed
     * @return array
     */
    protected function setDateInArray(Collection $transformed): array
    {
        $complete = $transformed->map(function ($collection, $key) {
            return array_merge($collection, ['date' => $key]);
        })->sortKeys();

        return array_values($complete->toArray());
    }

    /**
     * Get the load time given.
     *
     * @param \App\Models\Event $event
     * @param string|null $timestamp
     * @return \Illuminate\Support\Carbon
     */
    protected function getLoadTime(Event $event, string $timestamp = null): \Carbon\Carbon
    {
        return $timestamp === null ?
            $event->start_date :
            Carbon::createFromTimestampMs($timestamp)->floorMinutes(5);
    }

    /**
     * Get end load time. If event is over, it will display all points from beginning to end.
     *
     * @param \App\Models\Event $event
     * @param \Illuminate\Support\Carbon $loadTime
     * @param string|null $timestamp
     * @return \Illuminate\Support\Carbon
     */
    protected function getEndLoad(Event $event, Carbon $loadTime, string $timestamp = null): Carbon
    {
        if (DateHelper::eventAfter($event)) {
            return $event->end_date;
        }

        return $timestamp === null ?
            Carbon::now()->floorMinutes(5) : $loadTime->addMinutes(5);
    }

    /**
     * Get 5 minute time intervals.
     *
     * @param \Illuminate\Support\Carbon $startLoad
     * @param \Illuminate\Support\Carbon $endLoad
     * @param string|null $timestamp
     * @return \Illuminate\Support\Collection
     */
    protected function setTimeIntervals(Carbon $startLoad, Carbon $endLoad, string $timestamp = null): Collection
    {
        $start = $startLoad->copy();
        $end = $endLoad->copy();

        do {
            $intervals[] = $timestamp == null ? $start->copy()->format('Y-m-d H:i:s') :
                $start->copy()->addMinutes(5)->format('Y-m-d H:i:s');
            $timestamp = false;
            $start->addMinutes(5);
        } while ($start->lt($end));

        return collect($intervals)->flip();
    }
}