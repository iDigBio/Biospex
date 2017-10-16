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
     * Return the latest timestamp.
     *
     * @param $expeditionUuid
     * @return mixed
     */
    public function getLatestTimestamp($expeditionUuid)
    {
        return $this->weDigBioDashboardContract->setCacheLifetime(0)
            ->where('expedition_uuid', '=', $expeditionUuid)
            ->max('timestamp');
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
            $this->updateItem($transcription, $expedition, $classification);
    }

    private function createItem($transcription, $expedition)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $item = [
            'transcription_id'     => $transcription->id,
            'classification_id'    => $transcription->classification_id,
            'project_uuid'         => $expedition->project->uuid,
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
     * @param $expedition
     * @param WeDigBioDashboard $classification
     */
    private function updateItem($transcription, $expedition, $classification)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);

        $classification->transcription_id = $transcription->id;
        $classification->project_uuid = $expedition->project->uuid;
        $classification->timestamp = $transcription->classification_finished_at;
        $classification->subject['link'] = ! empty ($transcription->subject_references) ? $transcription->subject_references : $classification->subject['link'];
        $classification->subject['thumbnailUri'] = ! empty ($thumbnailUri) ? $thumbnailUri : $classification->subject['thumbnailUri'];
        $classification->contributor['transcriber'] = $transcription->user_name;
        $classification->transcriptionContent['country'] = ! empty($transcription->Country) ? $transcription->Country : $classification->country;
        $classification->transcriptionContent['province'] = ! empty($transcription->StateProvince) ? $transcription->StateProvince : $classification->transcriptionContent['province'];
        $classification->transcriptionContent['county'] = ! empty($transcription->County) ? $transcription->County : $classification->transcriptionContent['county'];
        $classification->transcriptionContent['locality'] = ! empty($transcription->Location) ? $transcription->Location : '';
        $classification->transcriptionContent['collector'] = ! empty($transcription->CollectedBy) ? $transcription->CollectedBy : '';
        $classification->transcriptionContent['taxon'] = ! empty($transcription->ScientificName) ? $transcription->ScientificName : $classification->taxon;

        $classification->save();
    }
}