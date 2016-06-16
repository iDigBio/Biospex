<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class FlushCacheEventListener
{
    /**
     * Create a new event listener.
     */
    public function __construct()
    {
    }

    public function handle()
    {
        if (Route::currentRouteName() == 'sessions.store') {
            return;
        }

        Cache::flush();
    }
}
