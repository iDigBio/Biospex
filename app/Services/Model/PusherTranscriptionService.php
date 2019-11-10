<?php

namespace App\Services\Model;

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesProject;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\PusherTranscription;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

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
     * @var \App\Repositories\Interfaces\PanoptesProject
     */
    private $panoptesProjectContract;

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
     * Check if dashboard document already exists.
     *
     * @param $transcription
     * @return int
     */
    public function checkPusherTranscription($transcription)
    {
        $exists = $this->pusherTranscriptionContract->findBy('transcription_id', $transcription->_id);

        return $exists === null;
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
        $classification === null ? $this->createClassification($transcription, $expedition) : $this->updateClassification($transcription, $classification);
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

        $item = [
            'transcription_id'     => $transcription->id,
            'classification_id'    => $transcription->classification_id,
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
     */
    private function updateClassification($transcription, $classification)
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
            'transcription_id'     => $transcription->_id,
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
}