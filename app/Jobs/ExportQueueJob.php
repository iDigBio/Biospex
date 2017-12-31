<?php

namespace App\Jobs;

use App\Models\ExportQueue as Model;
use App\Interfaces\ExportQueue;
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
    private $model;

    /**
     * ExportQueueJob constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Handle ExportQueue Job
     * @param ExportQueue $repository
     * @see \App\Observers\ExportQueueObserver Set when new export saved and event fired.
     */
    public function handle(ExportQueue $repository)
    {
        $queue = $repository->findByIdExpeditionActor($this->model->id, $this->model->expedition_id, $this->model->actor_id);

        $class = ActorFactory::create($queue->expedition->actor->class, $queue->expedition->actor->class);
        $class->queue($queue);

        $this->delete();
    }
}
