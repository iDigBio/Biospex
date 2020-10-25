<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Model;

use App\Jobs\ScoreboardJob;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTranscription;
use App\Repositories\Interfaces\EventUser;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use MongoDB\BSON\UTCDateTime;
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
    ) {
        $this->eventContract = $eventContract;
        $this->eventTranscriptionContract = $eventTranscriptionContract;
        $this->eventUserContract = $eventUserContract;
    }

    /**
     * Create event transcription for user.
     *
     * @param int $classification_id
     * @param int $projectId
     * @param string $userName
     * @param \MongoDB\BSON\UTCDateTime|null $date
     */
    public function createEventTranscription(
        int $classification_id,
        int $projectId,
        string $userName,
        UTCDateTime $date = null
    ) {
        $user = $this->eventUserContract->getEventUserByName($userName);

        if ($user === null) {
            return;
        }

        $timestamp = $this->setDate($date);

        $events = $this->eventContract->checkEventExistsForClassificationUserByDate($projectId, $user->id, $timestamp);

        $events->each(function ($event) use ($classification_id, $user) {
            $event->teams->each(function ($team) use ($event, $classification_id, $user) {
                $values = [
                    'classification_id' => $classification_id,
                    'event_id'          => $event->id,
                    'team_id'           => $team->id,
                    'user_id'           => $user->id,
                ];

                if ($this->validateClassification($values)) {
                    \Log::alert('Failed: ' . $classification_id);
                    return;
                }

                \Log::alert('Created: ' . $classification_id);
                $this->eventTranscriptionContract->create($values);
            });
        });

        if ($events->isNotEmpty()) {
            ScoreboardJob::dispatch($projectId);
        }
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
                return $query->where('classification_id', $values['classification_id'])->where('event_id', $values['event_id'])->where('team_id', $values['team_id'])->where('user_id', $values['user_id']);
            }),
        ]);

        return $validator->fails();
    }

    /**
     * Set date for creating event transcriptions.
     *
     * @param \MongoDB\BSON\UTCDateTime|null $date
     * @return string
     */
    private function setDate(UTCDateTime $date = null)
    {
        $timestamp = ! isset($date) ? Carbon::now('UTC') : $date->toDateTime();

        return $timestamp->format('Y-m-d H:i:s');
    }
}