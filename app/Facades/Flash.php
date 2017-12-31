<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Flash
 *
 * @method static \App\Services\Facades\Flash success(string $message)
 * @method static \App\Services\Facades\Flash info(string $message)
 * @method static \App\Services\Facades\Flash warning(string $message)
 * @method static \App\Services\Facades\Flash error(string $message)
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
        return 'flash';
    }
}