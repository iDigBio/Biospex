<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class CountHelper
 *
 * @package App\Facades
 */
class CountHelper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Services\Helpers\CountHelper::class;
    }
}