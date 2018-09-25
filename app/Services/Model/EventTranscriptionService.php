<?php

namespace App\Services\Model;

use App\Jobs\ScoreboardJob;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\EventUser;
use App\Services\Api\NfnApi;
use Cache;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Validator;

class EventTranscriptionService
{
    /**
     * @var \App\Services\Api\NfnApi
     */
    private $nfnApi;

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
     * @param \App\Services\Api\NfnApi $nfnApi
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param \App\Repositories\Interfaces\EventTranscription $eventTranscriptionContract
     * @param \App\Repositories\Interfaces\EventUser $eventUserContract
     */
    public function __construct(
        NfnApi $nfnApi,
        Event $eventContract,
        EventTranscription $eventTranscriptionContract,
        EventUser $eventUserContract
    )
    {
        $this->nfnApi = $nfnApi;
        $this->eventContract = $eventContract;
        $this->eventTranscriptionContract = $eventTranscriptionContract;
        $this->eventUserContract = $eventUserContract;
    }

    /**
     * Get nfn subject.
     *
     * @param $subjectId
     * @return null
     */
    public function getNfnSubject($subjectId)
    {
        $result = Cache::remember('subject-'.$subjectId, 60, function () use ($subjectId) {
            $this->nfnApi->setProvider();
            $this->nfnApi->checkAccessToken('nfnToken');
            $uri = $this->nfnApi->getSubjectUri($subjectId);
            $request = $this->nfnApi->buildAuthorizedRequest('GET', $uri);
            $results = $this->nfnApi->sendAuthorizedRequest($request);

            return isset($results['subjects'][0]) ? $results['subjects'][0] : null;
        });

        return $result;
    }

    /**
     * Get nfn user.
     *
     * @param $userId
     * @return mixed
     */
    public function getNfnUser($userId)
    {
        $result = Cache::remember('user-'.$userId, 60, function () use ($userId) {
            $this->nfnApi->setProvider();
            $this->nfnApi->checkAccessToken('nfnToken');
            $uri = $this->nfnApi->getUserUri($userId);
            $request = $this->nfnApi->buildAuthorizedRequest('GET', $uri);
            $results = $this->nfnApi->sendAuthorizedRequest($request);

            return isset($results['users'][0]) ? $results['users'][0] : null;
        });

        return $result['login'];
    }

    /**
     * Update or create event transcription for user.
     *
     * @param $data
     * @param $projectId
     */
    public function updateOrCreateEventTranscription($data, $projectId)
    {
        $user = $this->eventUserContract->getUserByName($data->user_name);

        $events = $this->eventContract->checkEventExistsForClassificationUser($projectId, $user->id);

        $events->filter(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);

            return Carbon::now($event->timezone)->between($start_date, $end_date) ? true : false;
        })->each(function ($event) use ($data, $user) {
            $event->teams->each(function ($team) use ($event, $data, $user) {

                $classificationId = $data->classification_id;
                $eventId = $event->id;
                $teamId = $team->id;
                $userId = $user->id;

                $values = [
                    'classification_id' => $classificationId,
                    'event_id'          => $eventId,
                    'team_id'           => $teamId,
                    'user_id'           => $userId,
                ];

                $validator = Validator::make($values, [
                    'classification_id' => Rule::unique('event_transcriptions')
                        ->where(function ($query) use($classificationId, $eventId, $teamId, $userId) {
                            return $query->where('classification_id', $classificationId)
                                ->where('event_id', $eventId)
                                ->where('team_id', $teamId)
                                ->where('user_id', $userId);
                        })
                ]);

                if ($validator->fails()) {
                    return;
                }

                $this->eventTranscriptionContract->create($values);
            });
        });

        if ($events->isNotEmpty()) {
            ScoreboardJob::dispatch($projectId);
        };
    }
}