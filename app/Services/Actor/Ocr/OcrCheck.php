<?php declare(strict_types=1);

namespace App\Services\Actor\Ocr;

use App\Repositories\Interfaces\OcrFile;
use App\Repositories\Interfaces\Subject;
use App\Models\OcrQueue;
use App\Notifications\OcrProcessComplete;
use App\Services\Csv\Csv;

class OcrCheck
{
    /**
     * @var Subject
     */
    private $subjectContract;

    /**
     * @var Csv
     */
    private $csvService;

    /**
     * @var \App\Repositories\Interfaces\OcrFile
     */
    private $ocrFile;

    /**
     * OcrCheck constructor.
     *
     * @param \App\Repositories\Interfaces\OcrFile $ocrFile
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\Csv\Csv $csvService
     */
    public function __construct(
        OcrFile $ocrFile,
        Subject $subjectContract,
        Csv $csvService
    ) {
        $this->ocrFile = $ocrFile;
        $this->subjectContract = $subjectContract;
        $this->csvService = $csvService;
    }

    /**
     * Process the record and send requests to ocr servers.
     *
     * @param $record
     * @throws \League\Csv\CannotInsertRecord
     * @throws \Exception
     */
    public function check($record)
    {
        $file = $this->ocrFile->find($record->mongo_id)->toArray();

        if (! $file) {
            throw new \Exception(trans('messages.ocr_file_error', [
                'title'   => $record->project->title,
                'id'      => $record->project->id,
                'message' => 'Json file could not be found.',
            ]));
        }

        if ($this->checkOcrFilePending($file)) {
            $this->updateProcessed($record, $file);

            return;
        }

        $csv = $this->updateSubjectsFromOcrFile($file);
        $this->setOcrCsv($record, $csv);
        $this->sendNotify($record);
        $this->ocrFile->delete($file['_id']);
        $record->delete();
        event('ocr.poll');
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $record
     * @param $csv
     */
    private function setOcrCsv($record, $csv)
    {
        $existing = $record->csv === false ? [] : $record->csv;
        $record->csv = $existing === null ? $csv : array_merge($existing, $csv);
        $record->save();
    }

    /**
     * Send notification for completed ocr process
     *
     * @param $record
     * @throws \League\Csv\CannotInsertRecord
     */
    private function sendNotify($record)
    {
        $csv = null;
        if (! empty($record->csv)) {
            $csv = config('config.reports_dir').'/'.str_random().'.csv';
            $this->csvService->writerCreateFromPath($csv);
            $headers = array_keys($record->csv[0]);
            $this->csvService->insertOne($headers);
            $this->csvService->insertAll($record->csv);
        }

        $record->project->group->owner->notify(new OcrProcessComplete($record->project->title, $csv));
    }

    /**
     * Update subjects processed.
     *
     * @param OcrQueue $record
     * @param $file
     */
    private function updateProcessed($record, $file)
    {
        $record->processed = (int) $file['header']['processed'];
        $record->save();
        event('ocr.poll');
    }

    /**
     * Update subject ocr fields based on ocr file results
     *
     * @param $file
     * @return array
     */
    public function updateSubjectsFromOcrFile($file)
    {
        $csv = [];
        foreach ($file['subjects'] as $subjectId => $data) {
            if ($data['ocr'] === 'error') {
                $csv[] = ['id' => $subjectId, 'messages' => $data['messages'], 'url' => $data['url']];
                continue;
            }

            $subject = $this->subjectContract->find($subjectId);
            if ($subject === null) {
                $csv[] = ['id'      => $subjectId,
                          'message' => 'Could not locate associated subject in database',
                          'url'     => '',
                ];
                continue;
            }

            $subject->ocr = $data['ocr'];
            $this->subjectContract->update($subject->toArray(), $subject->_id);
        }

        return $csv;
    }

    /**
     * Check ocr file status for progress
     *
     * @param $file
     * @return bool
     */
    public function checkOcrFilePending($file)
    {
        return $file['header']['status'] === 'pending';
    }
}