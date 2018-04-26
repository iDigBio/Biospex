<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Interfaces\Project;
use Illuminate\Console\Command;
use App\Repositories\Interfaces\OcrQueue;

class OcrPollCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes information for OCR Polling event.';

    /**
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * @var Project
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param OcrQueue $ocrQueueContract
     * @param Project $projectContract
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        Project $projectContract
    )
    {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->ocrQueueContract->getOcrQueuesForPollCommand();

        $data = ['message' => trans('pages.processing_empty'), 'payload' => []];

        if ($records->isEmpty())
        {
            PollOcrEvent::dispatch($data);

            return;
        }

        $grouped = $records->groupBy('ocr_csv_id')->toArray();

        $count = 0;
        foreach ($grouped as $group)
        {
            $project = $this->projectContract->findWith($group[0]['project_id'], ['group']);

            $total = array_sum(array_column($group, 'total'));
            $processed = array_sum(array_column($group, 'processed'));

            $batches = $count === 0 ? '' : trans_choice('pages.ocr_queue', $count, ['batches_queued' => $count]);

            $ocr = trans_choice('pages.ocr_records', $processed, ['processed' => $processed, 'total' => $total]);

            $notice = trans('pages.ocr_processing', ['title' => $project->title, 'ocr' => $ocr, 'batches' => $batches]);

            $data['payload'][] = [
                'groupId' => $project->group->id,
                'notice'   => $notice,
            ];

            $count++;
        }

        PollOcrEvent::dispatch($data);
    }
}
