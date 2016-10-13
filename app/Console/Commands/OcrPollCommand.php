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
     * @var int
     */
    private $batches = 0;

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

        $data = $this->loopGroupedRecords($grouped);

        $this->dispatcher->fire(new PollOcrEvent($data));
    }

    /**
     * Loop through grouped records.
     *
     * @param $grouped
     * @return array
     */
    private function loopGroupedRecords($grouped)
    {
        $data = [];
        foreach ($grouped as $group)
        {
            $project = $this->project->with(['group'])->find($group[0]['project_id']);

            $ocr = $this->setBatchText($group);

            $message = trans('pages.ocr_processing', ['title' => $project->title, 'ocr' => $ocr]);

            $data[] = [
                'groupUuid' => $project->group->uuid,
                'message'   => $message,
            ];
        }
        return $data;
    }

    /**
     * Set text for batch.
     *
     * @param $group
     * @return string
     */
    private function setBatchText($group)
    {
        $ocr = '';
        foreach ($group as $batch)
        {
            $records = $this->setRecordText($batch);

            $ocr .= trans_choice('pages.ocr_batches', $batch['processed'], [
                'batchId' => $this->batches === 0 ? 1 : $this->batches + 1 ,
                'records' => $records
            ]);

            $this->batches++;
        }

        return $ocr;
    }

    /**
     * Set the text for the OCR batch.
     *
     * @param $batch
     * @return string
     */
    private function setRecordText($batch)
    {
        if ($this->batches === 0)
        {
            $records = trans_choice('pages.ocr_records', $this->batches, [
                'processed' => $batch['processed'],
                'total'     => $batch['total']
            ]);
        }
        else
        {
            $records = trans_choice('pages.ocr_queue', $this->batches, ['batches_ahead' => $this->batches]);
        }

        return $records;
    }
}
