<?php

namespace App\Services\Model;

use App\Facades\DateHelper;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\WeDigBioDashboard;
use App\Services\Api\NfnApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class WeDigBioDashboardService
{

    /**
     * @var WeDigBioDashboard
     */
    private $weDigBioDashboardContract;

    /**
     * @var Expedition
     */
    private $expeditionContract;

    /**
     * @var PanoptesTranscription
     */
    private $panoptesTranscriptionContract;

    /**
     * @var NfnApi
     */
    private $nfnApi;

    /**
     * ExpeditionService constructor.
     * @param WeDigBioDashboard $weDigBioDashboardContract
     * @param Expedition $expeditionContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     * @param NfnApi $nfnApi
     */
    public function __construct(
        WeDigBioDashboard $weDigBioDashboardContract,
        Expedition $expeditionContract,
        PanoptesTranscription $panoptesTranscriptionContract,
        NfnApi $nfnApi
    )
    {
        $this->weDigBioDashboardContract = $weDigBioDashboardContract;
        $this->expeditionContract = $expeditionContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
        $this->nfnApi = $nfnApi;
    }

    /**
     * Get dashboard count
     *
     * @param $request
     * @return mixed
     */
    public function listApiDashboardCount($request)
    {
        return $this->weDigBioDashboardContract->getWeDigBioDashboardApi($request, true);
    }

    /**
     * List dashboard.
     *
     * @param Request $request
     * @return mixed
     */
    public function listApiDashboard($request)
    {
        return $this->weDigBioDashboardContract->getWeDigBioDashboardApi($request);
    }

    /**
     * Show single resource.
     *
     * @param $guid
     * @return mixed
     */
    public function showApiDashboard($guid)
    {
        return $this->weDigBioDashboardContract->where('guid', $guid)->first();
    }

    /**
     * Process classification data from Pusher.
     *
     * @param $data
     */
    public function processDataFromPusher($data)
    {
        $subject = $this->getNfnSubject($data->subject_ids[0]);

        $expedition = $this->getExpeditionBySubject($subject);

        if ($expedition === null){
            return;
        }

        $this->createDashboardFromPusher($data, $subject, $expedition);
    }

    /**
     * Get expedition.
     *
     * @param $subject
     * @return mixed|null
     */
    public function getExpeditionBySubject($subject)
    {
        if (empty($subject['metadata']['#expeditionId']))
        {
            return null;
        }

        return $this->expeditionContract->find($subject['metadata']['#expeditionId']);
    }

    /**
     * Get nfn subject.
     *
     * @param $subjectId
     * @return null
     */
    public function getNfnSubject($subjectId)
    {
        $result = Cache::remember('subject-' . $subjectId, 60, function () use ($subjectId) {
            $this->nfnApi->setProvider();
            $this->nfnApi->checkAccessToken('nfnToken');
            $uri = $this->nfnApi->getSubjectUri($subjectId);
            $request = $this->nfnApi->buildAuthorizedRequest('GET', $uri);
            $results = $this->nfnApi->sendAuthorizedRequest($request);

            return isset($results['subjects'][0]) ? $results['subjects'][0] : null;
        });

        return $result;
    }

    /**
     * Build item for dashboard.
     *
     * @param $data
     * @param $subject
     * @param $expedition
     *
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     *
     */
    private function createDashboardFromPusher($data, $subject, $expedition)
    {
        $thumbnailUri = $this->setPusherThumbnailUri($data);

        $item = [
            'transcription_id'     => '',
            'classification_id'    => $data->classification_id,
            'expedition_uuid'      => $expedition->uuid,
            'project'              => $expedition->title,
            'description'          => $expedition->description,
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => DateHelper::newMongoDbDate(),
            'subject'              => [
                'link'         => isset($subject['metadata']['references']) ? $subject['metadata']['references'] : '',
                'thumbnailUri' => $thumbnailUri
            ],
            'contributor'          => [
                'decimalLatitude'  => $data->geo->latitude,
                'decimalLongitude' => $data->geo->longitude,
                'ipAddress'        => '',
                'transcriber'      => '',
                'physicalLocation' => [
                    'country'      => $data->geo->country_name,
                    'province'     => '',
                    'county'       => '',
                    'municipality' => $data->geo->city_name,
                    'locality'     => ''
                ]
            ],
            'transcriptionContent' => [
                'lat'          => '',
                'long'         => '',
                'country'      => isset($subject['metadata']['country']) ? $subject['metadata']['country'] : '',
                'province'     => isset($subject['metadata']['stateProvince']) ? $subject['metadata']['stateProvince'] : '',
                'county'       => isset($subject['metadata']['county']) ? $subject['metadata']['county'] : '',
                'municipality' => '',
                'locality'     => '',
                'date'         => '', // which date to use? transcription date is messy
                'collector'    => '',
                'taxon'        => isset($subject['metadata']['scientificName']) ? $subject['metadata']['scientificName'] : '',
            ],
            'discretionaryState'   => 'Transcribed'
        ];

        $this->weDigBioDashboardContract->create($item);
    }

    /**
     * Determine image url.
     *
     * @param $data
     * @return mixed
     */
    public function setPusherThumbnailUri($data)
    {
        $imageUrl = (array) $data->subject_urls[0];

        return $imageUrl['image/jpeg'];
    }

    /**
     * Get expedition.
     *
     * @param $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getExpedition($expeditionId)
    {
        return $this->expeditionContract->find($expeditionId);
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
     * @param $transcriptionId
     * @return int
     */
    public function checkIfExists($transcriptionId)
    {
        $result = $this->weDigBioDashboardContract->findBy('transcription_id', $transcriptionId);

        return $result === null;
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
     * @param $transcription
     * @param $classification
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

        $this->weDigBioDashboardContract->update($attributes, $classification->_id);
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
}