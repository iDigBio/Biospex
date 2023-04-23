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

use Illuminate\Support\Carbon;
use DateTime;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

/**
 * Class DateHelper
 *
 * @package App\Services\Helpers
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
     * When doing raw queries for MongoDb, convert Carbon date to UTC.
     *
     * @param $date
     * @return \MongoDB\BSON\UTCDateTime
     */
    public function formatDateToUtcTimestamp($date)
    {
        return new UTCDateTime($date);
    }

    /**
     * Format date using timezone and format.
     *
     * @param $date
     * @param null $format
     * @param null $tz
     * @return \Illuminate\Support\Carbon|string
     */
    public function formatDate($date, $format = null, $tz = null)
    {
        if (is_null($date)) {
            return Carbon::now();
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->copy()->tz($tz)->format($format);
    }

    /**
     * Return timezone array for select box.
     *
     * @return array
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
     *
     * @param $timezone
     * @return string
     */
    public function eventRateChartTimezone($timezone): string
    {
        return str_replace('_', ' ', $timezone) . ' Timezone';
    }

    /**
     * Check event is before start date.
     *
     * @param $event
     * @param string|null $tz
     * @return bool
     */
    public function eventBefore($event, string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timzone : 'UTC';
        $start_date = $event->start_date->setTimezone($timezone);
        $now = \Carbon\Carbon::now($timezone);

        return $now->isBefore($start_date);
    }

    /**
     * Check event in progress.
     *
     * @param $event
     * @param string|null $tz
     * @return bool
     */
    public function eventActive($event, string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timzone : 'UTC';
        $start_date = $event->start_date->setTimezone($timezone);
        $end_date = $event->end_date->setTimezone($timezone);
        $now = Carbon::now($timezone);

        return $now->between($start_date, $end_date);
    }

    /**
     * Check if event is over.
     *
     * @param $event
     * @param string|null $tz
     * @return bool
     */
    public function eventAfter($event, string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timzone : 'UTC';
        $end_date = $event->end_date->setTimezone($timezone);
        $now = Carbon::now($timezone);

        return $now->gt($end_date);
    }
}