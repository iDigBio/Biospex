<?php

namespace App\Services\Model;

use App\Models\PanoptesTranscription;
use App\Models\WeDigBioDashboard;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\WeDigBioDashboardContract;
use Ramsey\Uuid\Uuid;

class WeDigBioDashboardService
{

    /**
     * @var WeDigBioDashboardContract
     */
    private $weDigBioDashboardContract;

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * @var PanoptesTranscriptionContract
     */
    private $panoptesTranscriptionContract;

    /**
     * ExpeditionService constructor.
     * @param WeDigBioDashboardContract $weDigBioDashboardContract
     * @param ExpeditionContract $expeditionContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     */
    public function __construct(
        WeDigBioDashboardContract $weDigBioDashboardContract,
        ExpeditionContract $expeditionContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract
    )
    {
        $this->weDigBioDashboardContract = $weDigBioDashboardContract;
        $this->expeditionContract = $expeditionContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
    }

    /**
     * Get expedition.
     *
     * @param $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getExpedition($expeditionId)
    {
        return $this->expeditionContract->setCacheLifetime(0)
            ->with('project')
            ->find($expeditionId);
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
        $query = $this->panoptesTranscriptionContract->setCacheLifetime(0)
            ->with(['subject' => function ($query) {
                $query->select('accessURI');
            }])
            ->where('subject_expeditionId', '=', $expeditionId);

        if ($timestamp !== null)
        {
            $query->where('classification_finished_at', '>=', $timestamp);
        }

        return $query->orderBy('classification_finished_at')->findAll();
    }

    /**
     * Check if dashboard document already exists.
     *
     * @param $transcriptionId
     * @return int
     */
    public function checkIfExists($transcriptionId)
    {
        return $this->weDigBioDashboardContract->setCacheLifetime(0)
            ->findWhere(['transcription_id', '=', $transcriptionId])->count();
    }

    /**
     * Process transcripts.
     *
     * @param $transcription
     * @param $expedition
     */
    public function processTranscripts($transcription, $expedition)
    {
        $classification = $this->weDigBioDashboardContract->findBy('classification_id', $transcription->classification_id);
        $this->buildItem($transcription, $expedition, $classification);
    }

    /**
     * Determine image url.
     *
     * @param $transcription
     * @return mixed
     */
    private function setThumbnailUri($transcription)
    {
        return ( ! isset($transcription->subject_imageURL) || empty($transcription->subject_imageURL)) ?
            $transcription->subject->accessURI : $transcription->subject_imageURL;
    }

    /**
     * Build item for dashboard.
     *
     * @param $transcription
     * @param $expedition
     * @param null $classification
     */
    private function buildItem($transcription, $expedition, $classification = null)
    {
        $classification === null ?
            $this->createItem($transcription, $expedition) :
            $this->updateItem($transcription, $classification);
    }

    private function createItem($transcription, $expedition)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $item = [
            'transcription_id'     => $transcription->id,
            'classification_id'    => $transcription->classification_id,
            'expedition_uuid'      => $expedition->uuid,
            'project'              => $transcription->workflow_name,
            'description'          => $expedition->description,
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => $transcription->classification_finished_at,
            'subject'              => [
                'link'         => $transcription->subject_references,
                'thumbnailUri' => $thumbnailUri
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
                    'locality'     => ''
                ]
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
            'discretionaryState'   => 'Transcribed'
        ];

        $this->weDigBioDashboardContract->create($item);
    }

    /**
     * @param PanoptesTranscription $transcription
     * @param WeDigBioDashboard $classification
     */
    private function updateItem($transcription, $classification)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $subject = [
            'link'         => ! empty ($transcription->subject_references) ? $transcription->subject_references : $classification->subject['link'],
            'thumbnailUri' => ! empty ($thumbnailUri) ? $thumbnailUri : $classification->subject['thumbnailUri']
        ];

        $transcriptionContent = [
            'country'   => ! empty($transcription->Country) ? $transcription->Country : $classification->country,
            'province'  => ! empty($transcription->StateProvince) ? $transcription->StateProvince : $classification->transcriptionContent['province'],
            'county'    => ! empty($transcription->County) ? $transcription->County : $classification->transcriptionContent['county'],
            'locality'  => ! empty($transcription->Location) ? $transcription->Location : '',
            'collector' => ! empty($transcription->CollectedBy) ? $transcription->CollectedBy : '',
            'taxon'     => ! empty($transcription->ScientificName) ? $transcription->ScientificName : $classification->taxon
        ];


        $attributes = [
            'transcription_id'     => $transcription->_id,
            'timestamp'            => $transcription->classification_finished_at,
            'subject'              => array_merge($classification->subject, $subject),
            'contributor'          => array_merge($classification->contributor, ['transcriber' => $transcription->user_name]),
            'transcriptionContent' => array_merge($classification->transcriptionContent, $transcriptionContent)
        ];

        $this->weDigBioDashboardContract->update($classification->_id, $attributes);
    }
}