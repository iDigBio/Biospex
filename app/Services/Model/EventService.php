<?php

namespace App\Services\Model;

use App\Jobs\EventBoardJob;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventGroup;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\EventUser;
use App\Repositories\Interfaces\Project;
use Auth;
use Carbon\Carbon;

class EventService
{
    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $event;

    /**
     * @var \App\Repositories\Interfaces\EventGroup
     */
    private $eventGroup;

    /**
     * @var \App\Repositories\Interfaces\EventUser
     */
    private $eventUser;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * @var \App\Repositories\Interfaces\EventTranscription
     */
    private $eventTranscription;

    /**
     * EventService constructor.
     *
     * @param \App\Repositories\Interfaces\Event $event
     * @param \App\Repositories\Interfaces\EventGroup $eventGroup
     * @param \App\Repositories\Interfaces\EventUser $eventUser
     * @param \App\Repositories\Interfaces\EventTranscription $eventTranscription
     * @param \App\Repositories\Interfaces\Project $project
     */
    public function __construct(
        Event $event,
        EventGroup $eventGroup,
        EventUser $eventUser,
        EventTranscription $eventTranscription,
        Project $project
    ) {

        $this->event = $event;
        $this->eventGroup = $eventGroup;
        $this->eventUser = $eventUser;
        $this->eventTranscription = $eventTranscription;
        $this->project = $project;
    }

    /**
     * Find event with relationships.
     *
     * @param $eventId
     * @param array $with
     * @return mixed
     */
    public function findEvent($eventId, array $with = [])
    {
        return $this->event->findWith($eventId, $with);
    }

    /**
     * Get index page information.
     *
     * @return mixed
     */
    public function getIndex()
    {
        return $this->event->getUserEvents(Auth::id());
    }

    /**
     * Get event for show page.
     *
     * @param $eventId
     * @return mixed
     */
    public function getShow($eventId)
    {
        return $this->event->getEventShow($eventId);
    }

    /**
     * Store event.
     *
     * @param $request
     * @return mixed
     */
    public function storeEvent($request)
    {
        return $this->event->createEvent($request);
    }

    /**
     * Edit event.
     *
     * @param $eventId
     * @return mixed
     */
    public function editEvent($eventId)
    {
        return $this->findEvent($eventId, ['groups']);
    }

    /**
     * Update event.
     *
     * @param $request
     * @param $eventId
     * @return mixed
     */
    public function updateEvent($request, $eventId)
    {
        return $this->event->updateEvent($request, $eventId);
    }

    /**
     * Delete event.
     *
     * @param $event
     * @return mixed
     */
    public function deleteEvent($event)
    {
        return $this->event->delete($event);
    }

    /**
     * Update or create event transcription for user.
     *
     * @param $data
     * @param $projectId
     */
    public function updateOrCreateEventTranscription($data, $projectId)
    {
        $events = $this->event->checkEventExistsForClassificationUser($projectId, $data->user_name);
        $filtered = $events->filter(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);

            return Carbon::now($event->timezone)->between($start_date, $end_date);
        })->each(function ($event) use ($data) {
            foreach ($event->groups as $group) {
                $attributes = ['classification_id' => $data->classification_id];
                $values = [
                    'classification_id' => $data->classification_id,
                    'event_id'          => $event->id,
                    'group_id'          => $group->id,
                    'user_id'           => $group->users->first()->id,
                ];

                $this->eventTranscription->updateOrCreate($attributes, $values);
            }
        });

        if ($filtered->isNotEmpty()) {
            EventBoardJob::dispatch($projectId);
        };
    }

    /**
     * Get group by uuid for invite page.
     *
     * @param $uuid
     * @return mixed
     */
    public function getGroupByUuid($uuid)
    {
        return $this->eventGroup->getGroupByUuid($uuid);
    }

    /**
     * Create or update user and assign to event group.
     *
     * @param \App\Http\Requests\EventJoinRequest $request
     * @return bool
     */
    public function updateOrCreateEventJoin($request)
    {
        $user = $this->eventUser->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $group = $this->eventGroup->find($request->get('group_id'));
            $group->users()->save($user);

            return true;
        }

        return false;
    }
}