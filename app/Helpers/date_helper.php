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
 */
function format_date(mixed $date, string $format = 'Y-m-d', string $tz = 'UTC'): \Carbon\Carbon|string
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->formatDate($date, $format, $tz);
}

/**
 * Return timezone array for select box.
 *
 *
 * @throws \Exception
 */
function time_zone_select(): array
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->timeZoneSelect();
}

/**
 * Return timezone title for event rate chart.
 */
function event_rate_chart_timezone(string $tz = 'UTC'): string
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->eventRateChartTimezone($tz);
}

/**
 * Check event is before start date.
 */
function event_before(mixed $event, ?string $tz = null): bool
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->eventBefore($event, $tz);
}

/**
 * Check if event is over.
 */
function event_after(mixed $event, ?string $tz = null): bool
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->eventAfter($event, $tz);
}

/**
 * Check event in progress.
 */
function event_active(mixed $event, ?string $tz = null): bool
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    return $dateHelper->eventActive($event, $tz);
}

/**
 * Set dates for event.
 */
function set_event_dates(array &$data): void
{
    $dateHelper = app(\App\Services\Helpers\DateService::class);

    $dateHelper->setEventDates($data);
}
