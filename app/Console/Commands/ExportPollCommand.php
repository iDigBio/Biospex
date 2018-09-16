<?php

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\ExportQueue;
use App\Services\Actor\ActorFactory;
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
     * @var Expedition
     */
    private $expeditionContract;
    /**
     * @var ExportQueue
     */
    private $exportQueueContract;

    /**
     * Create a new command instance.
     *
     * @param Expedition $expeditionContract
     * @param ExportQueue $exportQueueContract
     * @internal param Actor $actor
     */
    public function __construct(
        Expedition $expeditionContract,
        ExportQueue $exportQueueContract
    )
    {
        parent::__construct();

        $this->nfnActors = explode(',', config('config.nfnActors'));
        $this->expeditionContract = $expeditionContract;
        $this->exportQueueContract = $exportQueueContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->exportQueueContract->getAllExportQueueOrderByIdAsc();

        $data = ['message' => trans('html.processing_empty'), 'payload' => []];

        if ($records->isEmpty())
        {
            PollExportEvent::dispatch($data);
            return;
        }

        $count = 0;
        $data['payload'] = $records->map(function($record) use ($count) {

            $queue = $this->exportQueueContract->findQueueProcessData($record->id, $record->expedition_id, $record->actor_id);

            $actorClass = ActorFactory::create($queue->expedition->actor->class, $queue->expedition->actor->class . 'Export');
            $stage = $actorClass->stage[$queue->stage];
            $processed = $queue->expedition->actor->pivot->processed === 0 ? 1 : $queue->expedition->actor->pivot->processed;
            $total = $queue->expedition->actor->pivot->total;
            $count++;

            $notice = $record->queued ?
                trans('html.export_processing', [
                    'stage' => GeneralHelper::camelCaseToWords($stage),
                    'title' => $queue->expedition->title,
                    'processedRecords' => trans_choice('html.processed_records', $processed, [
                        'processed' => $processed,
                        'total' => $total
                    ]),
                ]) :
                trans_choice('html.export_queued', $count, [
                    'title' => $queue->expedition->title,
                    'count' => $count
                ]);

            return [
                'groupId'       => $queue->expedition->project->group->id,
                'notice'         => $notice
            ];
        })->toArray();

        PollExportEvent::dispatch($data);
    }
}
