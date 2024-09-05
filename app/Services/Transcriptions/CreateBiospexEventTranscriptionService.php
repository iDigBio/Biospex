<?php
/*
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

namespace App\Services\Transcriptions;

use App\Jobs\ScoreboardJob;
use App\Repositories\EventRepository;
use App\Repositories\EventTranscriptionRepository;
use App\Repositories\EventUserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Validator;

/**
 * Class CreateBiospexEventTranscriptionService
 *
 * @package App\Services\Transcriptions
 */
class CreateBiospexEventTranscriptionService
{
    /**
     * @var \App\Repositories\EventRepository
     */
    private EventRepository $eventRepo;

    /**
     * @var \App\Repositories\EventTranscriptionRepository
     */
    private EventTranscriptionRepository $eventTranscriptionRepo;

    /**
     * @var \App\Repositories\EventUserRepository
     */
    private EventUserRepository $eventUserRepo;

    /**
     * CreateBiospexEventTranscriptionService constructor.
     *
     * @param \App\Repositories\EventRepository $eventRepo
     * @param \App\Repositories\EventTranscriptionRepository $eventTranscriptionRepo
     * @param \App\Repositories\EventUserRepository $eventUserRepo
     */
    public function __construct(
        EventRepository $eventRepo,
        EventTranscriptionRepository $eventTranscriptionRepo,
        EventUserRepository $eventUserRepo
    ) {
        $this->eventRepo = $eventRepo;
        $this->eventTranscriptionRepo = $eventTranscriptionRepo;
        $this->eventUserRepo = $eventUserRepo;
    }

    /**
     * Create event transcription for user.
     *
     * @param int $classification_id
     * @param int $projectId
     * @param string $userName
     * @param \Illuminate\Support\Carbon|null $date
     */
    public function createEventTranscription(
        int $classification_id,
        int $projectId,
        string $userName,
        Carbon $date = null
    ): void {
        $user = $this->eventUserRepo->findBy('nfn_user', $userName, ['id']);

        if ($user === null) {
            return;
        }

        $timestamp = ! isset($date) ? Carbon::now('UTC') : $date;

        $events = $this->eventRepo->getAnyEventsForUserByProjectIdAndDate($projectId, $user->id, $timestamp->toDateTimeString());

        $events->each(function ($event) use ($classification_id, $user, $timestamp) {
            $event->teams->each(function ($team) use ($event, $classification_id, $user, $timestamp) {
                $attributes = [
                    'classification_id' => $classification_id,
                    'event_id'          => $event->id,
                    'team_id'           => $team->id,
                    'user_id'           => $user->id,
                ];

                if ($this->validateClassification($attributes)) {
                    return;
                }

                $values = array_merge($attributes, ['created_at' => $timestamp->toDateTimeString(), 'updated_at' => $timestamp->toDateTimeString()]);

                $this->eventTranscriptionRepo->create($values);
            });
        });

        if ($events->isNotEmpty() && !isset($date)) {
            ScoreboardJob::dispatch($projectId);
        }
    }

    /**
     * Validate classification.
     *
     * @param array $attributes
     * @return bool
     */
    private function validateClassification(array $attributes): bool
    {
        $validator = Validator::make($attributes, [
            'classification_id' => Rule::unique('event_transcriptions')->where(function ($query) use ($attributes) {
                return $query->where('classification_id', $attributes['classification_id'])->where('event_id', $attributes['event_id'])->where('team_id', $attributes['team_id'])->where('user_id', $attributes['user_id']);
            }),
        ]);

        // returns true if records exists
        return $validator->fails();
    }
}