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

        if (! $this->checkDate($wedigbioDate)) {
            return;
        }

        $values = [
            'classification_id' => $classification_id,
            'project_id'        => $projectId,
            'date_id'           => $wedigbioDate->id
        ];

        if ($this->validateClassification($values)) {
            return;
        }

        $this->weDigBioEventTranscriptionRepository->create($values);

        /**
         * TODO Create similar scoreboard upate job for wedigbio projects.
         *
        if ($events->isNotEmpty() && !isset($date)) {
            ScoreboardJob::dispatch($projectId);
        }
         */
    }

    /**
     * Validate classification.
     *
     * @param $values
     * @return bool
     */
    private function validateClassification($values): bool
    {
        $validator = Validator::make($values, [
            'classification_id' => Rule::unique('wedigbio_event_transactions')->where(function ($query) use ($values) {
                return $query
                    ->where('classification_id', $values['classification_id'])
                    ->where('project_id', $values['project_id'])
                    ->where('date_id', $values['date_id']);
            }),
        ]);
        // returns true if records exists
        return $validator->fails();
    }

    /**
     * Check date is between active WeDigbio Event Date.
     *
     * @param \App\Models\WeDigBioEventDate $weDigBioEventDate
     * @return bool
     */
    private function checkDate(WeDigBioEventDate $weDigBioEventDate): bool
    {
        $date = Carbon::now('UTC');

        return $date->between($weDigBioEventDate->start_date, $weDigBioEventDate->end_date);
    }
}