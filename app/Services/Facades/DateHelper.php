<?php

namespace App\Services\Facades;

use DateInterval;
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
    function mongoDbNowSubDateInterval($interval)
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
    function formatMongoDbDate($date, $format = 'Y-m-d')
    {
        return $date->toDateTime()->format($format);
    }
}