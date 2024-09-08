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

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

/**
 * Class DateService
 */
class DateService
{
    /**
     * Format date using timezone and format.
     */
    public function formatDate(mixed $date, string $format = 'Y-m-d', string $tz = 'UTC'): Carbon|string
    {
        if (! $date instanceof Carbon) {
            $date = is_string($date) ? Carbon::parse($date) : Carbon::now();
        }

        return $date->shiftTimezone($tz)->format($format);
    }

    /**
     * Return format for Mongo UTCDateTime.
     */
    public function formatMongoDate(UTCDateTime $date, string $format = 'Y-m-d', string $tz = 'UTC'): Carbon|string
    {
        $date = Carbon::instance($date->toDateTime());

        return $date->shiftTimezone($tz)->format($format);
    }

    /**
     * Return timezone array for select box.
     *
     *
     * @throws \Exception
     */
    public function timeZoneSelect(): array
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
    public function eventRateChartTimezone(string $tz): string
    {
        return str_replace('_', ' ', $tz).' Timezone';
    }

    /**
     * Check event is before start date.
     */
    public function eventBefore($event, ?string $tz = null): bool
    {
        $timezone = $tz === null ? $event->timezone : 'UTC';
        $start_date = $event->start_date->setTimezone($timezone);
        $now = Carbon::now($timezone);

        return $now->isBefore($start_date);
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
     * Set dates for event.
     */
    public function setEventDates(array &$data): void
    {
        $data['start_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['start_date'].':00', $data['timezone']);
        $data['end_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['end_date'].':00', $data['timezone']);
    }
}
