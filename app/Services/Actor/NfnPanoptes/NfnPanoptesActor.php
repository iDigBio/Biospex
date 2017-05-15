<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Services\Actor\ActorBase;

class NfnPanoptesActor extends ActorBase
{

    /**
     * @var NfnPanoptesExport
     */
    private $export;

    /**
     * @var NfnPanoptesClassifications
     */
    private $classifications;

    /**
     * NfnPanoptes constructor.
     * @param NfnPanoptesExport $export
     * @param NfnPanoptesClassifications $classifications
     */
    public function __construct(
        NfnPanoptesExport $export,
        NfnPanoptesClassifications $classifications
    )
    {
        $this->export = $export;
        $this->classifications = $classifications;
    }

    /**
     * Process actors.
     *
     * @param $actor
     * @throws BiospexException
     */
    public function process($actor)
    {
        if ($actor->pivot->state === 0) {
            $this->export->process($actor);
        }
        elseif ($actor->pivot->state === 1) {
            $this->classifications->process($actor);
        }
    }
}