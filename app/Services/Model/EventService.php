<?php

namespace App\Services\Model;

use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventGroup;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\EventUser;
use App\Repositories\Interfaces\Project;
use Auth;

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
        return $this->event->createEvent($request->all());
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
        return $this->event->updateEvent($request->all(), $eventId);
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
     * @param $expedition
     */
    public function updateOrCreateEventTranscription($data, $expedition)
    {
        if ($data->user_name === null) {
            return;
        }

        $event = $this->event->checkEventExistsForClassificationUser($expedition->project_id, $data->user_name);
        if ($event === null){
            return;
        }

        $attributes = ['classification_id' => $data->classification_id];
        $values = [
            'classification_id' => $data->classification_id,
            'event_id' => $event->event_id,
            'group_id' => $event->group_id,
            'user_id' => $event->user_id
        ];

        $this->eventTranscription->updateOrCreate($attributes, $values);
    }
}