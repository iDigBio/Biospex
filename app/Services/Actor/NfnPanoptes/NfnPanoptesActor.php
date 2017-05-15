<?php

namespace App\Services\Actor\NfnPanoptes;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\ExportQueueContract;
use App\Services\Actor\ActorBase;
use Illuminate\Events\Dispatcher as Event;

class NfnPanoptesActor extends ActorBase
{

    /**
     * @var NfnPanoptesClassifications
     */
    private $classifications;
    /**
     * @var ExportQueueContract
     */
    private $exportQueueContract;
    /**
     * @var Event
     */
    private $dispatcher;

    /**
     * NfnPanoptes constructor.
     * @param NfnPanoptesClassifications $classifications
     * @param ExportQueueContract $exportQueueContract
     * @param Event $dispatcher
     */
    public function __construct(
        NfnPanoptesClassifications $classifications,
        ExportQueueContract $exportQueueContract,
        Event $dispatcher
    )
    {
        $this->classifications = $classifications;
        $this->exportQueueContract = $exportQueueContract;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Process actors.
     *
     * @param $actor
     * @throws BiospexException
     */
    public function process($actor)
    {
        if ($actor->pivot->state === 0)
        {
            $this->exportQueueContract->firstOrCreate(['expedition_id' => $actor->pivot->expedition_id]);
            $this->dispatcher->fire('exportQueue.saved');
        }
        elseif ($actor->pivot->state === 1)
        {
            $this->classifications->process($actor);
        }
    }
}