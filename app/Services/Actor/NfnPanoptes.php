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
     * Process actor according to state.
     *
     * @param \App\Models\Actor $actor
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
     * Process export queue.
     *
     * @param \App\Models\ExportQueue $queue
     */
    public function processQueue(ExportQueue $queue)
    {
        $this->{$this->exportStages[$queue->stage]}->process($queue);
    }
}