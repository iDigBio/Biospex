<?php

namespace App\Services\Helpers;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

class DateHelper
{

    /**
     * Create new UTCDate.
     *
     * @return UTCDateTime
     */
    public function newMongoDbDate()
    {
        return new UTCDateTime(time() * 1000);
    }

    /**
     * @param $value
     * @return int|UTCDateTime|string
     */
    public function toMongoDbTimestamp($value)
    {
        return (is_numeric($value) && (int)$value == $value) ?
            new UTCDateTime($value * 1000) : $value;
    }

    /**
     * @param $interval
     * @return UTCDateTime
     */
    public function mongoDbNowSubDateInterval($interval)
    {
        $date = new \DateTime();
        $timestamp = $date->sub(new DateInterval($interval));

        return new UTCDateTime($timestamp);
    }

    /**
     * @param UTCDateTime $date
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
     * Convert timezone.
     *
     * @param $data
     * @param null $format
     * @param null $tz
     * @return string
     */
    public function convertTimeZone($data, $format = null, $tz = null)
    {
        $userTime = new DateTime($data, new DateTimeZone('UTC'));
        $userTime->setTimezone(new DateTimeZone($tz));
        return $userTime->format($format);
    }

    /**
     * Return timezone array for select box.
     *
     * @return array
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
}