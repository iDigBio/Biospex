<?php

namespace App\Services\Helpers;

use Illuminate\Support\Carbon;
use DateTime;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

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
     * @return mixed
     */
    public function formatDate($date, $format = null, $tz = null)
    {
        if (is_null($date)) {
            return Carbon::now();
        }

        return $date->copy()->tz($tz)->format($format);
    }

    /**
     * Return timezone array for select box.
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
     * Get years between two string dates, sort desc.
     *
     * @see \App\Services\Process\TranscriptionChartService
     * @param $begin
     * @param $end
     * @return \Illuminate\Support\Collection
     */
    public function getRangeInYearsDesc($begin, $end)
    {
        $years = range(Carbon::parse($begin)->year, Carbon::parse($end)->year);
        sort($years);

        return collect($years);
    }
}