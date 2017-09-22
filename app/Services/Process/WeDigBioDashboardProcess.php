<?php

namespace App\Services\Process;

use App\Repositories\Contracts\ExpeditionStatContract;
use App\Repositories\Contracts\WeDigBioDashboardContract;

class WeDigBioDashboardProcess
{

    /**
     * @var WeDigBioDashboardContract
     */
    private $weDigBioDashboardContract;

    /**
     * @var ExpeditionStatContract
     */
    private $expeditionStatContract;

    /**
     * WeDigBioDashboardProcess constructor.
     * @param WeDigBioDashboardContract $weDigBioDashboardContract
     * @param ExpeditionStatContract $expeditionStatContract
     */
    public function __construct(
        WeDigBioDashboardContract $weDigBioDashboardContract,
        ExpeditionStatContract $expeditionStatContract
    )
    {
        $this->weDigBioDashboardContract = $weDigBioDashboardContract;
        $this->expeditionStatContract = $expeditionStatContract;
    }

    public function process($transcription, $expedition)
    {
        $thumbnailUri = $this->setThumbnailUri($transcription);
        $item = $this->buildItem($transcription, $expedition, $thumbnailUri);


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
                'project'              => $expedition->title,
                'description'          => $expedition->description,
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