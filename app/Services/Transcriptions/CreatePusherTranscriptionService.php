<?php
/*
 * Copyright (c) 2022. Biospex
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

namespace App\Services\Transcriptions;

use App\Models\PusherClassification;
use App\Services\Models\PusherTranscriptionModelService;
use Validator;

/**
 * Class CreatePusherTranscriptionService
 */
class CreatePusherTranscriptionService
{
    public function __construct(
        protected PusherClassification $pusherClassification,
        protected PusherTranscriptionModelService $pusherTranscriptionModelService
    ) {}

    /**
     * Method called to start processing pusher classifications held in the MySql database to MongoDb
     *
     * @see \App\Jobs\PusherTranscriptionJob
     *
     * @return void
     */
    public function process()
    {
        $this->pusherClassification->chunkById(50, function ($chunk) {
            $chunk->each(function ($record) {
                $this->createDashboardRecord($record);
                $record->delete();
            });
        });
    }

    /**
     * Create dashboard item.
     * Uses classifications stored in database to relieve traffic on MongoDB.
     */
    public function createDashboardRecord(PusherClassification $pusherClassification)
    {

        if ($this->validateTranscription($pusherClassification->classification_id)) {
            return;
        }

        $this->pusherTranscriptionModelService->create($pusherClassification->data);
    }

    /**
     * Validate transcription to prevent duplicates.
     */
    public function validateTranscription($classification_id): mixed
    {

        $rules = ['classification_id' => 'unique:mongodb.pusher_transcriptions,classification_id'];
        $values = ['classification_id' => $classification_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        return $validator->fails();
    }
}
