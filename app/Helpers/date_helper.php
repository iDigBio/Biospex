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

/**
 * Format date using timezone and format.
 *
 * @param mixed $date
 * @param string $format
 * @param string $tz
 * @return \Carbon\Carbon|string
 */
if (! function_exists('format_date')) {

    function format_date(mixed $date, string $format = 'Y-m-d', string $tz = 'UTC')
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->formatDate($date, $format, $tz);
    }
}

/**
 * Return format for Mongo UTCDateTime milliseconds.
 *
 * @param \MongoDB\BSON\UTCDateTime $date
 * @param string $format
 * @param string $tz
 * @return \Carbon\Carbon|string
 */
if (! function_exists('format_mongo_date')) {

    function format_mongo_date(\MongoDB\BSON\UTCDateTime $date, string $format = 'Y-m-d', string $tz = 'UTC')
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->formatMongoDate($date, $format, $tz);
    }
}



/**
 * Return timezone array for select box.
 *
 * @return array
 * @throws \Exception
 */
if (! function_exists('time_zone_select')) {

    function time_zone_select()
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->timeZoneSelect();
    }
}

/**
 * Return timezone title for event rate chart.
 *
 * @param string $tz
 * @return string
 */
if (! function_exists('event_rate_chart_timezone')) {

    function event_rate_chart_timezone(string $tz = 'UTC')
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->eventRateChartTimezone($tz);
    }
}

/**
 * Check event is before start date.
 *
 * @param $event
 * @param string|null $tz
 * @return bool
 */
if (! function_exists('event_before')) {

    function event_before(mixed $event, string $tz = null)
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->eventBefore($event, $tz);
    }
}

/**
 * Check if event is over.
 *
 * @param $event
 * @param string|null $tz
 * @return bool
 */
if (! function_exists('event_after')) {

    function event_after(mixed $event, string $tz = null)
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->eventAfter($event, $tz);
    }
}

/**
 * Check event in progress.
 *
 * @param mixed $event
 * @param string|null $tz
 * @return bool
 */
if (! function_exists('event_active')) {

    function event_active(mixed $event, string $tz = null)
    {
        $dateHelper = app(\App\Services\Helpers\DateService::class);

        return $dateHelper->eventActive($event, $tz);
    }
}

