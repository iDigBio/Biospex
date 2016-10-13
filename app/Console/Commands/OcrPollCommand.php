<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
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
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param OcrQueue $ocrQueue
     * @param Dispatcher $dispatcher
     */
    public function __construct(OcrQueue $ocrQueue, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->ocrQueue = $ocrQueue;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->ocrQueue->skipCache()->with(['project.group'])->where(['error' => 0])->get();

        if ($records->isEmpty())
        {
            $data = trans('pages.processing_empty');
            $this->dispatcher->fire(new PollOcrEvent($data));

            return;
        }

        $grouped = $records->groupBy('ocr_csv_id')->toArray();

        $data = [];
        $totalSubjectsAhead = 1;
        $previousKey = null;
        foreach ($grouped as $key => $group)
        {
            if (null !== $previousKey)
            {
                $totalSubjectsAhead = +array_sum(array_column($grouped[$previousKey], 'processed'));
            }

            $previousKey = $key;

            $groupSubjectCount = array_sum(array_column($group, 'total'));
            $groupSubjectRemaining = array_sum(array_column($group, 'processed'));

            $message = trans('pages.ocr_processing', [
                'title'     => $group[0]['project']['title'],
                'batchId'   => $key,
                'remaining' => $groupSubjectRemaining,
                'total'     => $groupSubjectCount,
                'ahead'     => trans_choice('pages.ocr_in_queue', $totalSubjectsAhead, ['remaining_ahead' => $groupSubjectCount])
            ]);

            $data[] = [
                'groupUuid' => $group[0]['project']['group']['uuid'],
                'message'   => $message,
            ];
        }

        $this->dispatcher->fire(new PollOcrEvent($data));
    }
}
