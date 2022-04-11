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

namespace App\Services\Classifications;

use App\Repositories\ExpeditionRepository;
use App\Repositories\PanoptesTranscriptionRepository;
use App\Repositories\PusherTranscriptionRepository;
use Ramsey\Uuid\Uuid;
use Validator;

/**
 * Class PusherTranscriptionProcess
 *
 * @package App\Services\Process
 */
class PusherTranscriptionProcess
{
    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private $pusherTranscriptionRepo;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private $expeditionRepo;

    /**
     * @var \App\Repositories\PanoptesTranscriptionRepository
     */
    private $panoptesTranscriptionRepo;

    /**
     * PusherTranscriptionProcess constructor.
     *
     * @param \App\Repositories\PusherTranscriptionRepository $pusherTranscriptionRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Repositories\PanoptesTranscriptionRepository $panoptesTranscriptionRepo
     */
    public function __construct(
        PusherTranscriptionRepository $pusherTranscriptionRepo,
        ExpeditionRepository $expeditionRepo,
        PanoptesTranscriptionRepository $panoptesTranscriptionRepo
    ) {
        $this->pusherTranscriptionRepo = $pusherTranscriptionRepo;
        $this->expeditionRepo = $expeditionRepo;
        $this->panoptesTranscriptionRepo = $panoptesTranscriptionRepo;
    }

    /**
     * Get expedition.
     *
     * @param $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getExpedition($expeditionId)
    {
        return $this->expeditionRepo->findWith($expeditionId, ['panoptesProject']);
    }

    /**
     * Get transcriptions.
     *
     * @param $expeditionId
     * @param $timestamp
     * @return mixed
     */
    public function getTranscriptions($expeditionId, $timestamp = null)
    {
        return $this->panoptesTranscriptionRepo->getTranscriptionForDashboardJob($expeditionId, $timestamp);
    }

    /**
     * Process transcripts
     *
     * @param $transcription
     * @param $expedition
     * @throws \Exception
     */
    public function processTranscripts($transcription, $expedition)
    {
        $classification = $this->pusherTranscriptionRepo->findBy('classification_id', $transcription->classification_id);
        $classification === null ?
            $this->createClassification($transcription, $expedition) :
            $this->updateClassification($transcription, $classification, $expedition);
    }

    /**
     * Create classification if it doesn't exist.
     *
     * @param $transcription
     * @param $expedition
     * @throws \Exception
     */
    private function createClassification($transcription, $expedition)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $classification_id = (int) $transcription->classification_id;

        if ($this->validateTranscription($classification_id)) {
            return;
        }

        $item = [
            'classification_id'    => $classification_id,
            'expedition_uuid'      => $expedition->uuid,
            'project'              => $expedition->panoptesProject->title,
            'description'          => $expedition->description,
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => $transcription->classification_finished_at,
            'subject'              => [
                'link'         => $transcription->subject_references,
                'thumbnailUri' => $thumbnailUri,
            ],
            'contributor'          => [
                'decimalLatitude'  => '',
                'decimalLongitude' => '',
                'ipAddress'        => '',
                'transcriber'      => $transcription->user_name,
                'physicalLocation' => [
                    'country'      => '',
                    'province'     => '',
                    'county'       => '',
                    'municipality' => '',
                    'locality'     => '',
                ],
            ],
            'transcriptionContent' => [
                'lat'          => '',
                'long'         => '',
                'country'      => $transcription->Country,
                'province'     => $transcription->State_Province,
                'county'       => $transcription->County,
                'municipality' => '',
                'locality'     => $transcription->Location,
                'date'         => '', // which date to use? transcription date is messy
                'collector'    => $transcription->Collected_By,
                'taxon'        => $transcription->Scientific_Name,
            ],
            'discretionaryState'   => 'Transcribed',
        ];

        $this->pusherTranscriptionRepo->create($item);
    }

    /**
     * Update Classification.
     *
     * @param $transcription
     * @param $classification
     * @param $expedition
     */
    private function updateClassification($transcription, $classification, $expedition)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $subject = [
            'link'         => ! empty ($transcription->subject_references) ? $transcription->subject_references : $classification->subject['link'],
            'thumbnailUri' => ! empty ($thumbnailUri) ? $thumbnailUri : $classification->subject['thumbnailUri'],
        ];

        $transcriptionContent = [
            'country'   => ! empty($transcription->Country) ? $transcription->Country : $classification->country,
            'province'  => ! empty($transcription->StateProvince) ? $transcription->StateProvince : $classification->transcriptionContent['province'],
            'county'    => ! empty($transcription->County) ? $transcription->County : $classification->transcriptionContent['county'],
            'locality'  => ! empty($transcription->Location) ? $transcription->Location : '',
            'collector' => ! empty($transcription->CollectedBy) ? $transcription->CollectedBy : '',
            'taxon'     => ! empty($transcription->ScientificName) ? $transcription->ScientificName : $classification->taxon,
        ];

        $attributes = [
            'expedition_uuid'      => $expedition->uuid,
            'timestamp'            => $transcription->classification_finished_at,
            'subject'              => array_merge($classification->subject, $subject),
            'contributor'          => array_merge($classification->contributor, ['transcriber' => $transcription->user_name]),
            'transcriptionContent' => array_merge($classification->transcriptionContent, $transcriptionContent),
        ];

        $this->pusherTranscriptionRepo->update($attributes, $classification->_id);
    }

    /**
     * Determine image url.
     *
     * @param $transcription
     * @return mixed
     */
    private function setThumbnailUri($transcription)
    {
        return (empty($transcription->subject_imageURL)) ? $transcription->subject_accessURI : $transcription->subject_imageURL;
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $classification_id
     * @return mixed
     */
    public function validateTranscription($classification_id)
    {

        $rules = ['classification_id' => 'unique:mongodb.pusher_transcriptions,classification_id'];
        $values = ['classification_id' => $classification_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        return $validator->fails();
    }
}