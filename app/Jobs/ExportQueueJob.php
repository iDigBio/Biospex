<?php

namespace App\Jobs;

use App\Models\ExportQueue as Model;
use App\Repositories\Interfaces\ExportQueue;
use App\Services\Actor\ActorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\NfnExportError;

class ExportQueueJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 36000;

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
        $this->onQueue(config('config.export_tube'));
    }

    /**
     * Handle ExportQueue Job
     * @param ExportQueue $repository
     * @see \App\Observers\ExportQueueObserver Set when new export saved and event fired.
     */
    public function handle(ExportQueue $repository)
    {
        $queue = $repository->findByIdExpeditionActor($this->model->id, $this->model->expedition_id, $this->model->actor_id);

        try
        {
            $class = ActorFactory::create($queue->expedition->actor->class, $queue->expedition->actor->class);
            $class->queue($queue);
            $this->delete();
        }
        catch (\Exception $e)
        {
            event('actor.pivot.error', $queue->expedition->actor);

            $attributes = ['queued' => 0, 'error' => 1];
            $repository->update($attributes, $queue->id);

            $message = trans('messages.nfn_export_error', [
                'title'   => $queue->expedition->title,
                'id'      => $queue->expedition->id,
                'message' => $e->getFile() . ':' . $e->getLine() . ' - ' . $e->getMessage()
            ]);

            $queue->expedition->project->group->owner->notify(new NfnExportError($message));

            $this->delete();
        }
    }
}
