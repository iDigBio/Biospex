<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Contracts\ProjectContract;
use Illuminate\Console\Command;
use App\Repositories\Contracts\OcrQueueContract;

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
     * @var OcrQueueContract
     */
    private $ocrQueueContract;

    /**
     * @var ProjectContract
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param OcrQueueContract $ocrQueueContract
     * @param ProjectContract $projectContract
     */
    public function __construct(
        OcrQueueContract $ocrQueueContract,
        ProjectContract $projectContract
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
        $records = $this->ocrQueueContract->setCacheLifetime(0)
            ->where('error', '=', 0)
            ->orderBy('ocr_csv_id', 'asc')
            ->orderBy('created_at', 'asc')
            ->findAll();

        if ($records->isEmpty())
        {
            $data = trans('pages.processing_empty');
            event(new PollOcrEvent($data));

            return;
        }

        $grouped = $records->groupBy('ocr_csv_id')->toArray();

        $data = [];
        $count = 0;
        foreach ($grouped as $group)
        {
            $project = $this->projectContract->setCacheLifetime(0)
                ->with('group')->find($group[0]['project_id']);

            $total = array_sum(array_column($group, 'total'));
            $processed = array_sum(array_column($group, 'processed'));

            $batches = $count === 0 ? '' : trans_choice('pages.ocr_queue', $count, ['batches_queued' => $count]);

            $ocr = trans_choice('pages.ocr_records', $processed, ['processed' => $processed, 'total' => $total]);

            $message = trans('pages.ocr_processing', ['title' => $project->title, 'ocr' => $ocr, 'batches' => $batches]);

            $data[] = [
                'groupUuid' => $project->group->uuid,
                'message'   => $message,
            ];

            $count++;
        }

        event(new PollOcrEvent($data));
    }
}
