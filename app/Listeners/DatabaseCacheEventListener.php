<?php
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2/28/2017
 * Time: 1:30 PM
 */

namespace App\Listeners;


use Cache;

class DatabaseCacheEventListener
{
    /**
     * Flush cache on saved.
     */
    public function saved() {
        Cache::tags(['mysql'])->flush();
    }

    /**
     * Flush cache on deleted.
     */
    public function deleted()
    {
        Cache::tags(['mysql'])->flush();
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.saved: *',
            'App\Listeners\DatabaseCacheEventListener@saved'
        );

        $events->listen(
            'eloquent.deleted: *',
            'App\Listeners\DatabaseCacheEventListener@deleted'
        );
    }

}

