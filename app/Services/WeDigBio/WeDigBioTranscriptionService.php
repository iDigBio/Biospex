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

namespace App\Services\WeDigBio;

use App\Jobs\WeDigBioEventProgressJob;
use App\Models\WeDigBioEventDate;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Validator;

/**
 * Class WeDigBioTranscriptionService
 */
class WeDigBioTranscriptionService
{
    /**
     * EventTranscriptionService constructor.
     */
    public function __construct(
        protected WeDigBioService $weDigBioService,
        protected \Carbon\Carbon $carbon
    ) {}

    /**
     * Create event transcription for user.
     */
    public function createEventTranscription(
        int $classification_id,
        int $projectId,
        ?Carbon $date = null
    ): void {
        $wedigbioDate = $this->weDigBioService->weDigBioEventDate->where('active', 1)->first();

        $timestamp = ! isset($date) ? $this->carbon::now('UTC') : $date;

        if ($wedigbioDate === null || ! $this->checkDate($wedigbioDate, $timestamp)) {
            return;
        }

        $attributes = [
            'classification_id' => $classification_id,
            'project_id' => $projectId,
            'date_id' => $wedigbioDate->id,
        ];

        if ($this->validateClassification($attributes)) {
            return;
        }

        $values = array_merge($attributes, ['created_at' => $timestamp->toDateTimeString(), 'updated_at' => $timestamp->toDateTimeString()]);

        $this->weDigBioService->weDigBioEventTranscription->create($values);
        \Cache::forget('wedigbio-event-transcription');

        WeDigBioEventProgressJob::dispatch($wedigbioDate);
    }

    /**
     * Validate classification.
     */
    private function validateClassification($attributes): bool
    {
        $validator = Validator::make($attributes, [
            'classification_id' => Rule::unique('wedigbio_event_transcriptions')->where(function ($query) use ($attributes) {
                return $query
                    ->where('classification_id', $attributes['classification_id'])
                    ->where('project_id', $attributes['project_id'])
                    ->where('date_id', $attributes['date_id']);
            }),
        ]);

        // returns true if records exists
        return $validator->fails();
    }

    /**
     * Check date is between active WeDigbio Event Date.
     */
    private function checkDate(WeDigBioEventDate $weDigBioEventDate, Carbon $timestamp): bool
    {
        return $timestamp->between($weDigBioEventDate->start_date, $weDigBioEventDate->end_date);
    }
}
