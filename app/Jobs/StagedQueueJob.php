<?php

namespace App\Jobs;

use App\Listeners\StagedQueueEventListener;
use App\Models\StagedQueue;
use App\Repositories\Contracts\StagedQueueContract;
use App\Services\Actor\ActorFactory;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StagedQueueJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;
    /**
     * @var StagedQueue
     */
    private $record;

    /**
     * StagedQueueJob constructor.
     * @param StagedQueue $record
     */
    public function __construct(StagedQueue $record)
    {
        $this->record = $record;
    }

    /**
     * Handle StagedQueue Job
     * @param StagedQueueContract $stagedQueueContract
     * @see StagedQueueEventListener::stagedQueueSaved() Set when new export saved and event fired.
     */
    public function handle(StagedQueueContract $stagedQueueContract)
    {
        $queue = $stagedQueueContract->setCacheLifetime(0)
            ->findByIdExpeditionActor($this->record->id, $this->record->expedition_id, $this->record->actor_id);

        $class = ActorFactory::create($queue->expedition->actor);
        $class->queue($queue);
    }
}
