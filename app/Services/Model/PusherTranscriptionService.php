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

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\PusherTranscription;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Validator;

class PusherTranscriptionService
{
    /**
     * @var PusherTranscription
     */
    private $pusherTranscriptionContract;

    /**
     * @var Expedition
     */
    private $expeditionContract;

    /**
     * @var PanoptesTranscription
     */
    private $panoptesTranscriptionContract;

    /**
     * ExpeditionService constructor.
     *
     * @param PusherTranscription $pusherTranscriptionContract
     * @param Expedition $expeditionContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     */
    public function __construct(
        PusherTranscription $pusherTranscriptionContract,
        Expedition $expeditionContract,
        PanoptesTranscription $panoptesTranscriptionContract
    ) {
        $this->pusherTranscriptionContract = $pusherTranscriptionContract;
        $this->expeditionContract = $expeditionContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
    }

    /**
     * Get dashboard count
     *
     * @param $request
     * @return mixed
     */
    public function listApiDashboardCount($request)
    {
        return $this->pusherTranscriptionContract->getWeDigBioDashboardApi($request, true);
    }

    /**
     * List dashboard.
     *
     * @param Request $request
     * @return mixed
     */
    public function listApiDashboard($request)
    {
        return $this->pusherTranscriptionContract->getWeDigBioDashboardApi($request);
    }

    /**
     * Show single resource.
     *
     * @param $guid
     * @return mixed
     */
    public function showApiDashboard($guid)
    {
        return $this->pusherTranscriptionContract->where('guid', $guid)->first();
    }

    /**
     * Get expedition.
     *
     * @param $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getExpedition($expeditionId)
    {
        return $this->expeditionContract->findWith($expeditionId, ['panoptesProject']);
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
        return $this->panoptesTranscriptionContract->getTranscriptionForDashboardJob($expeditionId, $timestamp);
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
        $classification = $this->pusherTranscriptionContract->findBy('classification_id', $transcription->classification_id);
        $this->buildItem($transcription, $expedition, $classification);
    }

    /**
     * Build item for dashboard.
     *
     * @param $transcription
     * @param $expedition
     * @param null $classification
     * @throws \Exception
     */
    private function buildItem($transcription, $expedition, $classification = null)
    {
        $classification === null ? $this->createClassification($transcription, $expedition) : $this->updateClassification($transcription, $classification, $expedition);
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

        $this->pusherTranscriptionContract->create($item);
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

        $this->pusherTranscriptionContract->update($attributes, $classification->_id);
    }

    /**
     * Determine image url.
     *
     * @param $transcription
     * @return mixed
     */
    private function setThumbnailUri($transcription)
    {
        return (! isset($transcription->subject_imageURL) || empty($transcription->subject_imageURL)) ? $transcription->subject->accessURI : $transcription->subject_imageURL;
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