<?php namespace Biospex\Events;

use Cache;
use Route;

class FlushCacheEvent extends Event {

	/**
	 * Create a new event instance.
	 */
	public function __construct()
	{

	}

    public function handle()
    {
        if (Route::currentRouteName() == 'sessions.store')
            return;

        Cache::flush();
    }

}
