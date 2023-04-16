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

use App\Models\WeDigBioEventDate;
use App\Repositories\WeDigBioEventDateRepository;
use App\Repositories\WeDigBioEventTranscriptionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class WeDigBioEventRateChartProcess
 *
 * @package App\Services\Process
 */
class WeDigBioEventRateChartProcess
{
    /**
     * @var \App\Repositories\WeDigBioEventDateRepository
     */
    private WeDigBioEventDateRepository $weDigBioEventDateRepository;

    /**
     * @var \App\Repositories\WeDigBioEventTranscriptionRepository
     */
    private WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository;

    /**
     * AjaxService constructor.
     *
     * @param \App\Repositories\WeDigBioEventDateRepository $weDigBioEventDateRepository
     * @param \App\Repositories\WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository
     */
    public function __construct(
        WeDigBioEventDateRepository $weDigBioEventDateRepository,
        WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository
    ) {

        $this->weDigBioEventDateRepository = $weDigBioEventDateRepository;
        $this->weDigBioEventTranscriptionRepository = $weDigBioEventTranscriptionRepository;
    }

    /**
     * Get wedigbio event transcription data for step chart.
     *
     * @param int $dateId
     * @param string|null $timestamp
     * @return array|null
     */
    public function getWeDigBioEventRateChart(int $dateId, string $timestamp = null): ?array
    {
        $weDigBioDate = $this->weDigBioEventDateRepository->getByActiveOrDateId($dateId);

        if ($weDigBioDate === null) {
            return null;
        }

        $loadTime = $this->getLoadTime($weDigBioDate, $timestamp);

        $startLoad = $loadTime->copy();

        $endLoad = $this->getEndLoad($weDigBioDate, $loadTime, $timestamp);

        $intervals = $this->setTimeIntervals($startLoad, $endLoad, $timestamp);

        $transcriptions = $this->weDigBioEventTranscriptionRepository->getWeDigBioRateChartTranscriptions($weDigBioDate->id, $startLoad, $endLoad);

        $projects = $transcriptions->map(function($transcription){
            return $transcription->project->title;
        })->unique();

        return $transcriptions->isEmpty() ?
            $this->processEmptyResult($weDigBioDate->end_date, $projects, $intervals) :
            $this->processTranscriptionResult($projects, $transcriptions, $intervals);
    }

    /**
     * Process an empty result set as this means it's very beginning.
     *
     * @param \Illuminate\Support\Carbon $end_date
     * @param \Illuminate\Support\Collection $projects
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    public function processEmptyResult(Carbon $end_date, Collection $projects, Collection $intervals): array
    {
        if (Carbon::now('UTC')->gt($end_date)) {
            return [];
        }

        $data = [];
        $intervals->each(function ($interval, $key) use (&$data, $projects) {
            $projects->each(function ($project) use (&$data, $key) {
                $data[$key][$project] = 0;
            });
        });

        return $data;
    }

    /**
     * Process query data for results.
     *
     * @param \Illuminate\Support\Collection $projects
     * @param \Illuminate\Support\Collection $transcriptions
     * @param \Illuminate\Support\Collection $intervals
     * @return array
     */
    protected function processTranscriptionResult(
        Collection $projects,
        Collection $transcriptions,
        Collection $intervals
    ): array {
        $mapped = $this->mapWithDateKeys($transcriptions);

        $merged = $this->mergeIntervals($mapped, $intervals);

        $transformed = $this->addMissingProjectCount($projects, $merged);

        return $this->setDateInArray($transformed);
    }

    /**
     * Map projects and data using keys.
     * Count is per hour (count * 12) for each 5 minutes.
     *
     * @param \Illuminate\Support\Collection $transcriptions
     * @return \Illuminate\Support\Collection
     */
    protected function mapWithDateKeys(Collection $transcriptions): Collection
    {
        $data = [];
        $transcriptions->each(function ($transcription) use (&$data) {
            $date = Carbon::parse($transcription->time)->timezone('UTC')->format('Y-m-d H:i:s');
            $data[$date][$transcription->project->title] = $transcription->count * 12;
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
     * @param \Illuminate\Support\Collection $projects
     * @param \Illuminate\Support\Collection $merged
     * @return \Illuminate\Support\Collection
     */
    protected function addMissingProjectCount(Collection $projects, Collection $merged): Collection
    {
        return $merged->transform(function ($collection, $key) use ($projects) {
            $projects->each(function ($project) use (&$collection, $key) {
                if (! isset($collection[$project])) {
                    $collection[$project] = 0;
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
     * @param \App\Models\WeDigBioEventDate $weDigBioEventDate
     * @param string|null $timestamp
     * @return \Illuminate\Support\Carbon
     */
    protected function getLoadTime(WeDigBioEventDate $weDigBioEventDate, string $timestamp = null): Carbon
    {
        return $timestamp === null ?
            $weDigBioEventDate->start_date :
            Carbon::createFromTimestampMs($timestamp, 'UTC')->floorMinutes(5);
    }

    /**
     * Get end load time. If wedigbio event is over, it will display all points from beginning to end.
     *
     * @param \App\Models\WeDigBioEventDate $weDigBioEventDate
     * @param \Illuminate\Support\Carbon $loadTime
     * @param string|null $timestamp
     * @return \Illuminate\Support\Carbon
     */
    protected function getEndLoad(WeDigBioEventDate $weDigBioEventDate, Carbon $loadTime, string $timestamp = null): Carbon
    {
        $now = Carbon::now('UTC');
        if ($now->gt($weDigBioEventDate->end_date)) {
            return $weDigBioEventDate->end_date;
        }

        return $timestamp === null ?
            $now->floorMinutes(5) : $loadTime->addMinutes(5);
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
            $intervals[] = $timestamp == null ?
                $start->copy()->format('Y-m-d H:i:s') :
                $start->copy()->addMinutes(5)->format('Y-m-d H:i:s');
            $timestamp = false;
            $start->addMinutes(5);
        } while ($start->lt($end));

        return collect($intervals)->flip();
    }
}