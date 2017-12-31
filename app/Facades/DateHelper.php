<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class DateHelper
 *
 * @method static \App\Services\Facades\DateHelper newMongoDbDate()
 * @method static \App\Services\Facades\DateHelper toMongoDbTimestamp(int $value)
 * @method static \App\Services\Facades\DateHelper mongoDbNowSubDateInterval(string $interval)
 * @method static \App\Services\Facades\DateHelper formatMongoDbDate(int $data, string $format)
 *
 * @package App\Facades
 */
class DateHelper extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'datehelper';
    }
}