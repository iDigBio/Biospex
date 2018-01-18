<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class GeneralHelper
 * @package App\Facades
 */
class GeneralHelper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Services\Helpers\GeneralHelper::class;
    }
}