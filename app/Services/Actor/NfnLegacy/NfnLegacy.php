<?php

namespace App\Services\Actor\NfnLegacy;

use App\Models\Actor;
use App\Models\ExportQueue;
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
     * @param Actor $actor
     */
    public function actor(Actor $actor)
    {

    }

    /**
     * @param ExportQueue $queue
     */
    public function queue(ExportQueue $queue)
    {

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
        else
        {
            $actor->pivot->completed = 1;
            $actor->pivot->queued = 0;
            $actor->pivot->save();
        }
    }
}