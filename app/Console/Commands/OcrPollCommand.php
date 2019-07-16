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

        $data = ['message' => trans('pages.processes_none'), 'payload' => []];

        if ($records->isEmpty())
        {
            PollOcrEvent::dispatch($data);

            return;
        }

        $count = 0;
        $data['payload'] = $records->map(function($record) use (&$count){
            $batches = $count === 0 ? '' : trans_choice('html.ocr_queue', $count, ['batches_queued' => $count]);

            $countNumbers = ['processed' => $record->processed, 'total' => $record->total];
            $ocr = trans_choice('html.ocr_records', $record->processed, $countNumbers);

            $title = $record->expedition !== null ? $record->expedition->title : $record->project->title;
            $notice = trans('html.ocr_processing', ['title' => $title, 'ocr' => $ocr, 'batches' => $batches]);

            $count++;

            return [
                'groupId' => $record->project->group->id,
                'notice'   => $notice,
            ];
        })->toArray();

        PollOcrEvent::dispatch($data);
    }
}
