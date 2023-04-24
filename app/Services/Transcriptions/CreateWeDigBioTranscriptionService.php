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

use App\Events\WeDigBioProgressEvent;
use App\Jobs\WeDigBioEventProgressJob;
use App\Models\WeDigBioEventDate;
use App\Repositories\WeDigBioEventDateRepository;
use App\Repositories\WeDigBioEventTranscriptionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use MongoDB\BSON\UTCDateTime;
use Validator;

/**
 * Class CreateWeDigBioTranscriptionService
 *
 * @package App\Services\Transcriptions
 */
class CreateWeDigBioTranscriptionService
{
    /**
     * @var \App\Repositories\WeDigBioEventDateRepository
     */
    private WeDigBioEventDateRepository $weDigBioEventDateRepository;

    /**
     * @var \App\Repositories\WeDigBioEventTranscriptionRepository
     */
    private WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository;

    /**
     * CreateBiospexEventTranscriptionService constructor.
     *
     * @param \App\Repositories\WeDigBioEventDateRepository $weDigBioEventDateRepository
     * @param \App\Repositories\WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository
     */
    public function __construct(
        WeDigBioEventDateRepository $weDigBioEventDateRepository,
        WeDigBioEventTranscriptionRepository $weDigBioEventTranscriptionRepository
    ) {

        $this->weDigBioEventDateRepository = $weDigBioEventDateRepository;
        $this->weDigBioEventTranscriptionRepository = $weDigBioEventTranscriptionRepository;
    }

    /**
     * Create event transcription for user.
     *
     * @param int $classification_id
     * @param int $projectId
     * @param \MongoDB\BSON\UTCDateTime|null $date
     */
    public function createEventTranscription(
        int $classification_id,
        int $projectId,
        UTCDateTime $date = null
    ) {
        $wedigbioDate = $this->weDigBioEventDateRepository->findBy('active', 1);

        $timestamp = $this->setDate($date);

        if ($wedigbioDate === null || ! $this->checkDate($wedigbioDate, $timestamp)) {
            return;
        }

        $attributes = [
            'classification_id' => $classification_id,
            'project_id'        => $projectId,
            'date_id'           => $wedigbioDate->id
        ];

        if ($this->validateClassification($attributes)) {
            return;
        }

        $values = array_merge($attributes, ['created_at' => $timestamp->toDateTimeString(), 'updated_at' => $timestamp->toDateTimeString()]);

        $this->weDigBioEventTranscriptionRepository->create($values);
        \Cache::forget('wedigbio-event-transcription');

        WeDigBioEventProgressJob::dispatch($wedigbioDate->id);
    }

    /**
     * Validate classification.
     *
     * @param $attributes
     * @return bool
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
     * Set date for creating event transcriptions.
     *
     * @param \MongoDB\BSON\UTCDateTime|null $date
     * @return \Illuminate\Support\Carbon
     */
    private function setDate(UTCDateTime $date = null): Carbon
    {
        return ! isset($date) ? Carbon::now('UTC') : Carbon::createFromTimestampMsUTC($date);
    }

    /**
     * Check date is between active WeDigbio Event Date.
     *
     * @param \App\Models\WeDigBioEventDate $weDigBioEventDate
     * @param \Illuminate\Support\Carbon $timestamp
     * @return bool
     */
    private function checkDate(WeDigBioEventDate $weDigBioEventDate, Carbon $timestamp): bool
    {
        return $timestamp->between($weDigBioEventDate->start_date, $weDigBioEventDate->end_date);
    }
}