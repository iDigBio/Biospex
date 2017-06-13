<?php

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ExportQueueContract;
use App\Services\Actor\ActorFactory;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Command;

class ExportPollCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var string
     */
    private $nfnActors;

    /**
     * @var \Illuminate\Foundation\Application|\Laravel\Lumen\Application|mixed
     */
    private $dispatcher;

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;
    /**
     * @var ExportQueueContract
     */
    private $exportQueueContract;

    /**
     * Create a new command instance.
     *
     * @param ExpeditionContract $expeditionContract
     * @param Dispatcher $dispatcher
     * @param ExportQueueContract $exportQueueContract
     * @internal param Actor $actor
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        Dispatcher $dispatcher,
        ExportQueueContract $exportQueueContract
    )
    {
        parent::__construct();

        $this->nfnActors = explode(',', config('config.nfnActors'));
        $this->expeditionContract = $expeditionContract;
        $this->dispatcher = $dispatcher;
        $this->exportQueueContract = $exportQueueContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->exportQueueContract->setCacheLifetime(0)
            ->orderBy('id', 'asc')
            ->findAll();

        if ($records->isEmpty())
        {
            $data = trans('pages.processing_empty');
            $this->dispatcher->fire(new PollExportEvent($data));

            return;
        }

        $count = 0;
        $data = $records->map(function($record) use ($count) {

            $queue = $this->exportQueueContract->setCacheLifetime(0)
                ->findQueueProcessData($record->id, $record->expedition_id, $record->actor_id);

            $actorClass = ActorFactory::create($queue->expedition->actor->class, $queue->expedition->actor->class . 'Export');
            $stage = $actorClass->stage[$queue->stage];
            $processed = $queue->expedition->actor->pivot->processed === 0 ? 1 : $queue->expedition->actor->pivot->processed;
            $total = $queue->expedition->actor->pivot->total;
            $count++;

            $message = $record->queued ?
                trans('pages.export_processing', [
                    'stage' => camelCaseToWords($stage),
                    'title' => $queue->expedition->title,
                    'processedRecords' => trans_choice('pages.processed_records', $processed, [
                        'processed' => $processed,
                        'total' => $total
                    ]),
                ]) :
                trans_choice('pages.export_queued', $count, [
                    'title' => $queue->expedition->title,
                    'count' => $count
                ]);

            return [
                'groupUuid'       => $queue->expedition->project->group->uuid,
                'message'         => $message
            ];
        });

        $this->dispatcher->fire(new PollExportEvent($data->toArray()));
    }
}
