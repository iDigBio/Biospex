<?php

namespace App\Services\Actor\Ocr;

use App\Models\OcrQueue;
use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\Subject;
use App\Notifications\OcrProcessComplete;
use App\Services\Csv\Csv;
use Illuminate\Support\Str;

class OcrComplete extends OcrBase
{
    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFileContract;

    /**
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csvService;

    /**
     * OcrComplete constructor.
     *
     * @param \App\Repositories\Interfaces\OcrFile $ocrFileContract
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        OcrFile $ocrFileContract,
        Subject $subjectContract,
        Csv $csvService
    ) {

        $this->ocrFileContract = $ocrFileContract;
        $this->subjectContract = $subjectContract;
        $this->csvService = $csvService;
    }

    /**
     * Process the record and send requests to ocr servers.
     *
     * @param \App\Models\OcrQueue $queue
     * @throws \League\Csv\CannotInsertRecord
     */
    public function process(OcrQueue $queue)
    {
        $files = $this->ocrFileContract->getAllOcrQueueFiles($queue->id);

        $this->setDir($queue->id);
        $csv = $this->updateSubjects($files);
        $this->setOcrCsv($queue, $csv);
        $this->sendNotify($queue);
        $queue->ocrFiles()->delete();
        $this->deleteDir();

        event('ocr.poll');
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $queue
     * @param $csv
     */
    private function setOcrCsv($queue, $csv)
    {
        $existing = $queue->csv === false ? [] : $queue->csv;
        $queue->csv = $existing === null ? $csv : array_merge($existing, $csv);
        $queue->save();
    }


    /**
     * Update subject ocr fields based on ocr file results
     *
     * @param \Illuminate\Database\Eloquent\Collection $files
     * @return array
     */
    public function updateSubjects($files)
    {
        $csv = [];
        $files->each(function($file) use ($csv) {
            if ($file->ocr === 'error') {
                $csv[] = ['id' => $file->subject_id, 'messages' => $file->messages, 'url' => $file->url];
                return;
            }

            $subject = $this->subjectContract->find($file->subject_id);
            if ($subject === null) {
                $csv[] = ['id'      => $file->subject_id,
                          'message' => 'Could not locate associated subject in database',
                          'url'     => '',
                ];
                return;
            }

            $subject->ocr = $file->ocr;
            $this->subjectContract->update($subject->toArray(), $subject->_id);
        });

        return $csv;
    }

    /**
     * Send notification for completed ocr process
     *
     * @param $queue
     * @throws \League\Csv\CannotInsertRecord
     */
    private function sendNotify($queue)
    {
        $csv = null;
        if (! empty($queue->csv)) {
            $csv = config('config.reports_dir').'/'.Str::random().'.csv';
            $this->csvService->writerCreateFromPath($csv);
            $headers = array_keys($queue->csv[0]);
            $this->csvService->insertOne($headers);
            $this->csvService->insertAll($queue->csv);
        }

        $queue->project->group->owner->notify(new OcrProcessComplete($queue->project->title, $csv));
    }
}