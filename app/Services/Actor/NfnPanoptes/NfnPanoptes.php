<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Models\Actor;
use App\Models\ExportQueue;
use App\Services\Actor\ActorBase;
use App\Services\Queue\ActorQueue;

class NfnPanoptes extends ActorBase
{

    /**
     * @var NfnPanoptesExport
     */
    private $nfnPanoptesExport;

    /**
     * @var NfnPanoptesClassifications
     */
    private $nfnPanoptesClassifications;

    /**
     * NfnPanoptes constructor.
     *
     * @param NfnPanoptesExport $nfnPanoptesExport
     * @param NfnPanoptesClassifications $nfnPanoptesClassifications
     */
    public function __construct(
        NfnPanoptesExport $nfnPanoptesExport,
        NfnPanoptesClassifications $nfnPanoptesClassifications
    )
    {
        $this->nfnPanoptesExport = $nfnPanoptesExport;
        $this->nfnPanoptesClassifications = $nfnPanoptesClassifications;
    }

    /**
     * @inheritdoc
     * @see ActorQueue::fire()
     */
    public function actor(Actor $actor)
    {
        if ($actor->pivot->state === 0)
        {
            $this->nfnPanoptesExport->exportQueue($actor);
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
    public function queue(ExportQueue $queue)
    {
        $this->nfnPanoptesExport->queue($queue);
    }
}