<?php

namespace App\Listeners;

/**
 * Class BingoEventSubscriber
 *
 * @package App\Listeners
 */
class BingoEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'bingo.update',
            'App\Listeners\BingoEventSubscriber@update'
        );
    }

    /**
     *
     */
    public function update()
    {

    }
}