<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Event;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{

    /**
     * Displays Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Event $eventContract)
    {
        $results = $eventContract->getEventPublicIndex();

        list($events, $eventsCompleted) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        return view('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(Event $eventContract)
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $type = request()->get('type');
        $sort = request()->get('sort');
        $order = request()->get('order');

        $results = $eventContract->getEventPublicIndex($sort, $order);

        list($active, $completed) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        $events = $type === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }
}
