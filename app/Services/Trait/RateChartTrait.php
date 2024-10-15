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

namespace App\Services\Trait;

use App\Models\Event;
use App\Models\WeDigBioEventDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait RateChartTrait
{
    /**
     * Set the date in the array and keys to numeric.
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
     */
    protected function getLoadTime(Event|WeDigBioEventDate $event, Carbon $carbon, ?string $timestamp = null): \Carbon\Carbon
    {
        return $timestamp === null ?
            $event->start_date :
            $carbon::createFromTimestampMs($timestamp)->floorMinutes(5);
    }

    /**
     * Get 5 minute time intervals.
     */
    protected function setTimeIntervals(Carbon $startLoad, Carbon $endLoad, ?string $timestamp = null): Collection
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
