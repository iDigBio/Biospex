<?php

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\ExportQueue;
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
     * @var ExportQueue
     */
    private $exportQueueContract;

    /**
     * @var array
     */
    private $exportStages;

    /**
     * Create a new command instance.
     *
     * @param ExportQueue $exportQueueContract
     * @internal param Actor $actor
     */
    public function __construct(ExportQueue $exportQueueContract)
    {
        parent::__construct();

        $this->exportQueueContract = $exportQueueContract;
        $this->exportStages = config('config.export_stages');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = $this->exportQueueContract->getAllExportQueueOrderByIdAsc();

        $data = ['message' => trans('pages.processes_none'), 'payload' => []];

        if ($queues->isEmpty())
        {
            PollExportEvent::dispatch($data);
            return;
        }

        $count = 0;
        $data['payload'] = $queues->map(function($queue) use(&$count) {
            $data = $this->getFirstQueueData($queue);

            $notice = $queue->queued ?
                $this->setProcessNotice($data) :
                $this->setQueuedNotice($data, $count);

            $count++;

            return [
                'groupId' => $data->expedition->project->group->id,
                'notice'  => $notice
            ];
        })->values();

        PollExportEvent::dispatch($data);
    }

    /**
     * Get needed data for queue relationships.
     *
     * @param \App\Models\ExportQueue $queue
     * @return \App\Models\ExportQueue
     */
    private function getFirstQueueData(\App\Models\ExportQueue $queue): \App\Models\ExportQueue
    {
        return $this->exportQueueContract->findQueueProcessData($queue->id, $queue->expedition_id, $queue->actor_id);
    }

    /**
     * Set notice if process is occurring.
     *
     * @param \App\Models\ExportQueue $data
     * @return string
     */
    private function setProcessNotice(\App\Models\ExportQueue $data): string
    {
        $stage = $this->exportStages[$data->stage];

        $processed = $data->expedition->actor->pivot->processed === 0 ? 1 : $data->expedition->actor->pivot->processed;

        return trans('html.export_processing', [
            'stage'            => GeneralHelper::camelCaseToWords($stage),
            'title'            => $data->expedition->title,
            'processedRecords' => trans_choice('html.processed_records', $processed, [
                'processed' => $processed,
                'total'     => $data->count
            ])
        ]);
    }

    /**
     * Set notice message for remaining exports.
     *
     * @param \App\Models\ExportQueue $data
     * @param int $count
     * @return string
     */
    private function setQueuedNotice(\App\Models\ExportQueue $data, int $count): string
    {
        return trans('html.export_queued', [
            'title' => $data->expedition->title,
            'remainingCount' => trans_choice('html.export_remaining_count', $count, [
                'count' => $count
            ]),
        ]);
    }
}
