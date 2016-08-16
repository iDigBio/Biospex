<?php

namespace App\Services\Actor\NfnLegacy;

use App\Services\Actor\ActorInterface;

class NfnLegacy implements ActorInterface
{

    /**
     * @var NfnLegacyExport
     */
    private $export;

    /**
     * NfnLegacy constructor.
     * @param NfnLegacyExport $export
     */
    public function __construct(NfnLegacyExport $export)
    {
        $this->export = $export;
    }

    /**
     * @param $actor
     * @return mixed
     * @throws \Exception|\RuntimeException
     */
    public function process($actor)
    {
        if ($actor->pivot->state === 0)
        {
            $this->export->process($actor);
        }

    }
}