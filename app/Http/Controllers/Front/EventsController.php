<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Event;

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
        $events = $eventContract->getEventPublicPage();
        $eventsCompleted = $eventContract->getEventCompletedPublicPage();

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

        $name = request()->get('name');
        $sort = request()->get('sort');
        $order = request()->get('order');

        $events = $name === 'active' ?
            $eventContract->getEventPublicPage($sort, $order) :
            $eventContract->getEventCompletedPublicPage($sort, $order);

        return view('front.event.partials.event', compact('events'));
    }
}
