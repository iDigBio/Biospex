<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Contracts\Project;
use Illuminate\Console\Command;
use App\Repositories\Contracts\OcrQueue;
use Illuminate\Events\Dispatcher;

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
    private $ocrQueue;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param OcrQueue $ocrQueue
     * @param Project $project
     * @param Dispatcher $dispatcher
     */
    public function __construct(OcrQueue $ocrQueue, Project $project, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->ocrQueue = $ocrQueue;
        $this->project = $project;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->ocrQueue->skipCache()
            ->where(['error' => 0])
            ->orderBy(['ocr_csv_id' => 'asc', 'created_at' => 'asc'])
            ->get();

        if ($records->isEmpty())
        {
            $data = trans('pages.processing_empty');
            $this->dispatcher->fire(new PollOcrEvent($data));

            return;
        }

        $grouped = $records->groupBy('ocr_csv_id')->toArray();

        $data = [];
        $count = 0;
        foreach ($grouped as $group)
        {
            $project = $this->project->skipCache()->with(['group'])->find($group[0]['project_id']);

            $total = array_sum(array_column($group, 'total'));
            $processed = array_sum(array_column($group, 'processed'));

            $batches = $count === 0 ? '' : trans_choice('pages.ocr_queue', $count, ['batches_queued' => $count]);

            $ocr = trans_choice('pages.ocr_records', $processed,['processed' => $processed, 'total' => $total]);

            $message = trans('pages.ocr_processing', ['title' => $project->title, 'ocr' => $ocr, 'batches' => $batches]);

            $data[] = [
                'groupUuid' => $project->group->uuid,
                'message'   => $message,
            ];

            $count++;
        }

        $this->dispatcher->fire(new PollOcrEvent($data));
    }
}
