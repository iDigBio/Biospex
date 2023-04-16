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
use App\Repositories\PusherClassificationRepository;
use App\Repositories\PusherTranscriptionRepository;
use Validator;

/**
 * Class CreatePusherTranscriptionService
 *
 * @package App\Services\Transcriptions
 */
class CreatePusherTranscriptionService
{
    /**
     * @var \App\Repositories\PusherClassificationRepository
     */
    private PusherClassificationRepository $pusherClassificationRepo;

    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private PusherTranscriptionRepository $pusherTranscriptionRepository;

    /**
     * @param \App\Repositories\PusherClassificationRepository $pusherClassificationRepo
     * @param \App\Repositories\PusherTranscriptionRepository $pusherTranscriptionRepository
     */
    public function __construct(
        PusherClassificationRepository $pusherClassificationRepo,
        PusherTranscriptionRepository $pusherTranscriptionRepository)
    {

        $this->pusherClassificationRepo = $pusherClassificationRepo;
        $this->pusherTranscriptionRepository = $pusherTranscriptionRepository;
    }

    /**
     * Method called to start processing pusher classifications held in the MySql database to MongoDb
     *
     * @see \App\Jobs\PusherTranscriptionJob
     * @return void
     */
    public function process()
    {
        $this->pusherClassificationRepo->model->chunkById(50, function ($chunk) {
            $chunk->each(function ($record) {
                $this->createDashboardRecord($record);
                $record->delete();
            });
        });
    }

    /**
     * Create dashboard item.
     * Uses classifications stored in database to relieve traffic on MongoDB.
     *
     * @param \App\Models\PusherClassification $pusherClassification
     */
    public function createDashboardRecord(PusherClassification $pusherClassification)
    {

        if ($this->validateTranscription($pusherClassification->classification_id)) {
            \Log::alert($pusherClassification->classification_id . ' failed validation');
            return;
        }

        $this->pusherTranscriptionRepository->create($pusherClassification->data);
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $classification_id
     * @return mixed
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