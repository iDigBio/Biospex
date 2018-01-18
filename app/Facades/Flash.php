<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Flash
 *
 * @package App\Facades
 */
class Flash extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Services\Helpers\Flash::class;
    }
}