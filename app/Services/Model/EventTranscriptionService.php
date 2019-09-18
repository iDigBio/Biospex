<?php

namespace App\Services\Model;

use App\Jobs\ScoreboardJob;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\EventUser;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Validator;

class EventTranscriptionService
{
    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * @var \App\Repositories\Interfaces\EventTranscription
     */
    private $eventTranscriptionContract;

    /**
     * @var \App\Repositories\Interfaces\EventUser
     */
    private $eventUserContract;

    /**
     * EventTranscriptionService constructor.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param \App\Repositories\Interfaces\EventTranscription $eventTranscriptionContract
     * @param \App\Repositories\Interfaces\EventUser $eventUserContract
     */
    public function __construct(
        Event $eventContract,
        EventTranscription $eventTranscriptionContract,
        EventUser $eventUserContract
    )
    {
        $this->eventContract = $eventContract;
        $this->eventTranscriptionContract = $eventTranscriptionContract;
        $this->eventUserContract = $eventUserContract;
    }

    /**
     * Update or create event transcription for user.
     *
     * @param $data
     * @param $projectId
     * @param $user
     */
    public function updateOrCreateEventTranscription($data, $projectId, $user)
    {
        $user = $this->eventUserContract->getEventUserByName($user['login']);

        if ($user === null) {
            return;
        }

        $events = $this->eventContract->checkEventExistsForClassificationUser($projectId, $user);

        $events->filter(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);

            return Carbon::now($event->timezone)->between($start_date, $end_date) ? true : false;
        })->each(function ($event) use ($data, $user) {
            $event->teams->each(function ($team) use ($event, $data, $user) {
                $values = [
                    'classification_id' => $data->classification_id,
                    'event_id'          => $event->id,
                    'team_id'           => $team->id,
                    'user_id'           => $user->id,
                ];

                if ($this->validateClassification($values)) {
                    return;
                }

                $this->eventTranscriptionContract->create($values);
            });
        });

        if ($events->isNotEmpty()) {
            ScoreboardJob::dispatch($projectId);
        };
    }

    /**
     * Validate classification.
     *
     * @param $values
     * @return bool
     */
    private function validateClassification($values)
    {
        $validator = Validator::make($values, [
            'classification_id' => Rule::unique('event_transcriptions')->where(function ($query) use ($values) {
                return $query->where('classification_id', $values['classification_id'])
                    ->where('event_id', $values['event_id'])
                    ->where('team_id', $values['team_id'])
                    ->where('user_id', $values['user_id']);
            })
        ]);

        return $validator->fails();
    }
}