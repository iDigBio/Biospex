<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{

    use DispatchesJobs;
    public $nfnActors;
    public $expeditionContract;
    public $dispatcher;
    public $exportQueueContract;


    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * TestAppCommand constructor.
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
     *
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

        dd($data);

    }
}