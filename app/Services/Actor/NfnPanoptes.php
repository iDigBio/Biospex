<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Models\ExportQueue;

/**
 * Class NfnPanoptes
 *
 * @package App\Services\Actor
 */
class NfnPanoptes
{

    /**
     * @var NfnPanoptesClassifications
     */
    private $nfnPanoptesClassifications;

    /**
     * @var NfnPanoptesExportQueue
     */
    private $nfnPanoptesExportQueue;

    /**
     * @var array
     */
    public $exportStages;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportRetrieveImages
     */
    private $retrieveImages;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportConvertImages
     */
    private $convertImages;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportDeleteOriginalImages
     */
    private $deleteOriginalImages;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportBuildCsv
     */
    private $buildCsv;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportTarImages
     */
    private $tarImages;

    /**
     * @var \App\Services\Actor\NfnPanoptesExportReport
     */
    private $report;

    /**
     * NfnPanoptes constructor.
     *
     * @param \App\Services\Actor\NfnPanoptesClassifications $nfnPanoptesClassifications
     * @param \App\Services\Actor\NfnPanoptesExportQueue $nfnPanoptesExportQueue
     * @param \App\Services\Actor\NfnPanoptesExportRetrieveImages $retrieveImages
     * @param \App\Services\Actor\NfnPanoptesExportConvertImages $convertImages
     * @param \App\Services\Actor\NfnPanoptesExportDeleteOriginalImages $deleteOriginalImages
     * @param \App\Services\Actor\NfnPanoptesExportBuildCsv $buildCsv
     * @param \App\Services\Actor\NfnPanoptesExportTarImages $tarImages
     * @param \App\Services\Actor\NfnPanoptesExportReport $report
     */
    public function __construct(
        NfnPanoptesClassifications $nfnPanoptesClassifications,
        NfnPanoptesExportQueue $nfnPanoptesExportQueue,
        NfnPanoptesExportRetrieveImages $retrieveImages,
        NfnPanoptesExportConvertImages $convertImages,
        NfnPanoptesExportDeleteOriginalImages $deleteOriginalImages,
        NfnPanoptesExportBuildCsv $buildCsv,
        NfnPanoptesExportTarImages $tarImages,
        NfnPanoptesExportReport $report
    )
    {
        $this->nfnPanoptesClassifications = $nfnPanoptesClassifications;
        $this->nfnPanoptesExportQueue = $nfnPanoptesExportQueue;
        $this->retrieveImages = $retrieveImages;
        $this->convertImages = $convertImages;
        $this->deleteOriginalImages = $deleteOriginalImages;
        $this->buildCsv = $buildCsv;
        $this->tarImages = $tarImages;
        $this->report = $report;

        $this->exportStages = config('config.export_stages');
    }

    /**
     * @inheritdoc
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actor(Actor $actor)
    {
        if ($actor->pivot->state === 0)
        {
            $this->nfnPanoptesExportQueue->createQueue($actor);
        }
        elseif ($actor->pivot->state === 1)
        {
            $this->nfnPanoptesClassifications->processActor($actor);
        }
    }

    /**
     * @inheritdoc
     * @see ExportQueueJob::handle() Instantiates class and calls method.
     */
    public function processQueue(ExportQueue $queue)
    {
        $this->{$this->exportStages[$queue->stage]}->process($queue);
    }
}