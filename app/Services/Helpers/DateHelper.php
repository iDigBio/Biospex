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

namespace App\Services\Helpers;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;

/**
 * Class DateHelper
 */
class DateHelper
{
    /**
     * Format date from mongo db.
     *
     * @param $date
     * @param string $format
     * @return mixed
     */
    public function formatMongoDbDate($date, $format = 'Y-m-d')
    {
        return $date->toDateTime()->format($format);
    }

    /**
     * Format date using timezone and format.
     *
     * @param  null  $format
     * @param  null  $tz
     */
    public function formatDate($date, $format = null, $tz = null): Carbon|string
    {
        if (is_null($date)) {
            return Carbon::now();
        }

        if (! $date instanceof Carbon) {
            return Carbon::parse($date, $tz)->format($format);
        }

        return $date->copy()->tz($tz)->format($format);
    }

    /**
     * Return timezone array for select box.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function timeZoneSelect()
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        $timezone_offsets = [];
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        $timezone_list = [];
        foreach ($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));

            $pretty_offset = "UTC{$offset_prefix}{$offset_formatted}";

            $timezone_list[$timezone] = "({$pretty_offset}) $timezone";
        }

        return $timezone_list;
    }

    /**
     * Return timezone title for event rate chart.
     */
    public function eventRateChartTimezone($timezone): string
    {
        return str_replace('_', ' ', $timezone).' Timezone';
    }

    /**
     * Check event is before start date.
     */
    public function eventBefore($event, ?string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timezone : 'UTC';
        $start_date = $event->start_date->setTimezone($timezone);
        $now = \Carbon\Carbon::now($timezone);

        return $now->isBefore($start_date);
    }

    /**
     * Check event in progress.
     */
    public function eventActive($event, ?string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timezone : 'UTC';
        $start_date = $event->start_date->setTimezone($timezone);
        $end_date = $event->end_date->setTimezone($timezone);
        $now = Carbon::now($timezone);

        return $now->between($start_date, $end_date);
    }

    /**
     * Check if event is over.
     */
    public function eventAfter($event, ?string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timezone : 'UTC';
        $end_date = $event->end_date->setTimezone($timezone);
        $now = Carbon::now($timezone);

        return $now->gt($end_date);
    }
}
