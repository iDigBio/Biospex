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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Event;

use App\Jobs\ScoreboardJob;
use App\Services\Models\EventModel;
use App\Services\Models\EventTranscriptionModelService;
use App\Services\Models\EventUserModelService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Validator;

/**
 * Class EventTranscriptionService
 * TODO: Try to break into smaller classes to avoid DI count.
 */
class EventTranscriptionService
{
    /**
     * EventTranscriptionService constructor.
     */
    public function __construct(
        protected EventModel $eventModel,
        protected EventTranscriptionModelService $eventTranscriptionModelService,
        protected EventUserModelService $eventUserModelService,
        protected Carbon $carbon,
        protected ScoreboardJob $scoreboardJob
    ) {}

    /**
     * Create event transcription for user.
     */
    public function createEventTranscription(
        int $classification_id,
        int $projectId,
        string $userName,
        ?Carbon $date = null
    ): void {
        $user = $this->eventUserModelService->findByNfnUser($userName, ['id']);

        if ($user === null) {
            return;
        }

        $timestamp = ! isset($date) ? $this->carbon::now('UTC') : $date;

        $events = $this->eventModel->getAnyEventsForUserByProjectIdAndDate($projectId, $user->id, $timestamp->toDateTimeString());

        $events->each(function ($event) use ($classification_id, $user, $timestamp) {
            $event->teams->each(function ($team) use ($event, $classification_id, $user, $timestamp) {
                $attributes = [
                    'classification_id' => $classification_id,
                    'event_id' => $event->id,
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                ];

                if ($this->validateClassification($attributes)) {
                    return;
                }

                $values = array_merge($attributes, ['created_at' => $timestamp->toDateTimeString(), 'updated_at' => $timestamp->toDateTimeString()]);

                $this->eventTranscriptionModelService->create($values);
            });
        });

        if ($events->isNotEmpty() && ! isset($date)) {
            $this->scoreboardJob::dispatch($projectId);
        }
    }

    /**
     * Validate classification.
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
