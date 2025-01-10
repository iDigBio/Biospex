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

use App\Models\TranscriptionLocation;
use App\Services\Helpers\GeneralService;

use function config;

/**
 * Class CreateTranscriptionLocationService
 */
class CreateTranscriptionLocationService
{
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $dwcTranscriptFields;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $dwcOccurrenceFields;

    /**
     * CreateTranscriptionLocationService constructor.
     */
    public function __construct(
        protected TranscriptionLocation $transcriptionLocation,
        protected StateCountyService $stateCountyService,
        protected GeneralService $generalService
    ) {
        $this->dwcTranscriptFields = config('config.dwcTranscriptFields');
        $this->dwcOccurrenceFields = config('config.dwcOccurrenceFields');
    }

    /**
     * Build and create transcription location.
     *
     * @param  $subject
     *
     * Transcripts: StateProvince, County
     * Subject: stateProvince, county
     */
    public function buildTranscriptionLocation($transcription, $subject, $expeditionId)
    {
        $data = [];
        $this->setDwcLocalityFields($transcription, $subject, $data);

        if (array_key_exists('state_province', $data) && strtolower($data['state_province']) === 'district of columbia') {
            $data['county'] = $data['state_province'];
        }

        if (! $this->checkRequiredStateCounty($data)) {
            return;
        }

        $this->prepCounty($data);
        $stateAbbr = $this->generalService->getState($data['state_province']);
        $stateResult = $this->stateCountyService->findByCountyState($data['county'], $stateAbbr);

        if ($stateResult === null) {
            return;
        }

        $values['classification_id'] = $transcription['classification_id'];
        $values['project_id'] = $subject->project_id;
        $values['expedition_id'] = $expeditionId;
        $values['state_county_id'] = $stateResult->id;
        $attributes = ['classification_id' => $transcription['classification_id']];

        $this->transcriptionLocation->updateOrCreate($attributes, $values);
    }

    /**
     * Check locality fields from transcription.
     */
    private function setDwcLocalityFields($transcription, $subject, &$data): array
    {
        $this->setDwcLocalityFromTranscript($transcription, $data);
        $this->setDwcLocalityFromOccurrence($subject, $data);

        return $data;

    }

    /**
     * Set the dwc locality fields using transcript.
     */
    private function setDwcLocalityFromTranscript($transcription, &$data)
    {
        foreach ($this->dwcTranscriptFields as $transcriptField => $mapField) {
            if (isset($transcription[$transcriptField]) && ! empty($transcription[$transcriptField])) {
                $data[$mapField] = $transcription[$transcriptField];
            }
        }
    }

    /**
     * Set the dwc locality fields using occurrence.
     */
    private function setDwcLocalityFromOccurrence($subject, &$data)
    {
        if (count($data) == 2) {
            return;
        }

        foreach ($this->dwcOccurrenceFields as $occurrenceField => $mapField) {
            if (isset($subject->occurrence->{$occurrenceField}) && ! empty($subject->occurrence->{$occurrenceField})) {
                $data[$mapField] = $subject->occurrence->{$occurrenceField};
            }
        }
    }

    /**
     * Check if state and county exist.
     *
     * @return bool
     */
    private function checkRequiredStateCounty($data)
    {
        if (! isset($data['state_province']) || ! isset($data['county'])) {
            return false;
        }

        if (empty($data['state_province']) || empty($data['county'])) {
            return false;
        }

        return true;
    }

    /**
     * Prep County for searching database.
     */
    private function prepCounty(&$data)
    {
        $county = trim(preg_replace("/[^ \w-]/", '', $data['county']));
        $search = ['Saint', 'Sainte', 'Miami Dade', 'De Soto', 'De Kalb', 'county', 'City', 'Not Shown'];
        $replace = ['St.', 'Ste.', 'Miami-Dade', 'DeSoto', 'DeKalb', '', '', ''];
        $county = trim(str_ireplace($search, $replace, $county));
        $data['county'] = $county;
    }
}
