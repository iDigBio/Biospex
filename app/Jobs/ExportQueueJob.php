<?php

namespace App\Jobs;

use App\Listeners\ExportQueueEventListener;
use App\Models\ExportQueue;
use App\Repositories\Contracts\ExportQueueContract;
use App\Services\Actor\ActorFactory;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportQueueJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels;
    /**
     * @var ExportQueue
     */
    private $record;

    /**
     * ExportQueueJob constructor.
     * @param ExportQueue $record
     */
    public function __construct(ExportQueue $record)
    {
        $this->record = $record;
    }

    /**
     * Handle ExportQueue Job
     * @param ExportQueueContract $exportQueueContract
     * @see ExportQueueEventListener::exportQueueSaved() Set when new export saved and event fired.
     */
    public function handle(ExportQueueContract $exportQueueContract)
    {
        $queue = $exportQueueContract->setCacheLifetime(0)
            ->findByIdExpeditionActor($this->record->id, $this->record->expedition_id, $this->record->actor_id);

        $class = ActorFactory::create($queue->expedition->actor);
        $class->queue($queue);
    }
}
