<?php

namespace App\Services\Model;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\WeDigBioDashboardContract;
use App\Services\Process\WeDigBioDashboardProcess;
use Ramsey\Uuid\Uuid;

class WeDigBioDashboardService
{

    /**
     * @var WeDigBioDashboardContract
     */
    private $weDigBioDashboardContract;

    /**
     * @var WeDigBioDashboardProcess
     */
    private $weDigBioDashboardProcess;

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
     * @param WeDigBioDashboardProcess $weDigBioDashboardProcess
     * @param ExpeditionContract $expeditionContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     */
    public function __construct(
        WeDigBioDashboardContract $weDigBioDashboardContract,
        WeDigBioDashboardProcess $weDigBioDashboardProcess,
        ExpeditionContract $expeditionContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract
    )
    {
        $this->weDigBioDashboardContract = $weDigBioDashboardContract;
        $this->weDigBioDashboardProcess = $weDigBioDashboardProcess;
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
     * @param $expeditionId
     * @return mixed
     */
    public function getLatestTimestamp($expeditionId)
    {
        return $this->weDigBioDashboardContract->setCacheLifetime(0)
            ->where('expedition_id', '=', $expeditionId)
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
        $thumbnailUri = $this->setThumbnailUri($transcription);
        $item = $this->buildItem($transcription, $expedition, $thumbnailUri);
        $this->weDigBioDashboardContract->create($item);
    }

    /**
     * Determine image url.
     *
     * @param $transcription
     * @return mixed
     */
    private function setThumbnailUri($transcription)
    {
        return ( ! isset($transcription['subject_imageURL']) || empty($transcription['subject_imageURL'])) ?
            $transcription->subject->accessURI : $transcription['subject_imageURL'];
    }

    /**
     * Build item for dashboard.
     *
     * @param $transcription
     * @param $expedition
     * @param $thumbnailUri
     * @return array
     */
    private function buildItem($transcription, $expedition, $thumbnailUri)
    {
        return [
            'transcription_id'     => $transcription->id,
            'project_uuid'         => $expedition->project->uuid,
            'expedition_uuid'      => $expedition->uuid,
            'project'              => $transcription->workflow_name,
            'description'          => $expedition->description,
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => $transcription['classification_finished_at'],
            'subject'              => [
                'link'         => $transcription['subject_references'],
                'thumbnailUri' => $thumbnailUri
            ],
            'contributor'          => [
                'decimalLatitude'  => '',
                'decimalLongitude' => '',
                'ipAddress'        => '',
                'transcriber'      => $transcription['user_name'],
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
                'country'      => $transcription['Country'],
                'province'     => $transcription['State_Province'],
                'county'       => $transcription['County'],
                'municipality' => '',
                'locality'     => $transcription['Location'],
                'date'         => '', // which date to use? transcription date is messy
                'collector'    => $transcription['Collected_By'],
                'taxon'        => $transcription['Scientific_Name'],
            ],
            'discretionaryState'   => 'Transcribed'
        ];
    }
}