<?php declare(strict_types=1);

namespace App\Services\Process;

use App\Interfaces\Subject;
use App\Models\OcrQueue;
use App\Notifications\OcrProcessComplete;
use App\Services\Csv\Csv;
use App\Services\Requests\OcrRequest;

class OcrProcess
{

    /**
     * @var Subject
     */
    private $subjectContract;
    
    /**
     * @var OcrRequest
     */
    private $ocrRequest;
    
    /**
     * @var Csv
     */
    private $csvService;

    /**
     * OcrProcess constructor.
     * @param Subject $subjectContract
     * @param OcrRequest $ocrRequest
     * @param Csv $csvService
     */
    public function __construct(
        Subject $subjectContract,
        OcrRequest $ocrRequest,
        Csv $csvService
    )
    {

        $this->subjectContract = $subjectContract;
        $this->ocrRequest = $ocrRequest;
        $this->csvService = $csvService;
    }

    /**
     * Process the record and send requests to ocr servers
     * @param OcrQueue $record
     * @throws \Exception
     * @throws \League\Csv\CannotInsertRecord
     * @throws \TypeError
     */
    public function process($record)
    {
        if ( ! $record->status)
        {
            $this->ocrRequest->sendOcrFile($record);

            $record->status = 1;
            $record->save();

            return;
        }

        $file = $this->ocrRequest->requestOcrFile($record->uuid);

        if ( ! $this->ocrRequest->checkOcrFileHeaderExists($file))
        {
            return;
        }

        if ($this->ocrRequest->checkOcrFileInProgress($file))
        {
            $this->updateProcessed($record, $file);

            return;
        }

        if ($this->ocrRequest->checkOcrFileError($file))
        {
            throw new \Exception(trans('errors.ocr_file_error', [
                'title' => $record->title,
                'id' => $record->id,
                'message' => 'Json file header returned status error.'
            ]));
        }

        $this->updateProcessed($record, $file);

        $csv = $this->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($record, $csv);

        $record->status = 2;
        $record->save();

        if ($record->batch)
        {
            $this->sendNotify($record);
            $record->ocrCsv->delete();
        }

        $this->ocrRequest->deleteJsonFiles([$record->uuid . '.json']);
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $record
     * @param $csv
     */
    private function setOcrCsv($record, $csv)
    {
        $subjects = $record->ocrCsv->subjects === '' ? [] : $record->ocrCsv->subjects;
        $record->ocrCsv->subjects = $subjects === null ? $csv : array_merge($subjects, $csv);
        $record->ocrCsv->save();
    }

    /**
     * Send notification for completed ocr process
     *
     * @param $record
     * @throws \League\Csv\CannotInsertRecord
     * @throws \TypeError
     */
    private function sendNotify($record)
    {
        $csv = null;
        if ( ! empty($record->ocrCsv->subjects))
        {
            $csv = config('config.export_reports_dir') . '/' . str_random() . '.csv';
            $this->csvService->writerCreateFromPath($csv);
            $headers = array_keys($record->ocrCsv->subjects[0]);
            $this->csvService->insertOne($headers);
            $this->csvService->insertAll($record->ocrCsv->subjects);
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
        $record->processed = (int) $file->header->complete;
        $record->save();
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
        foreach ($file->subjects as $subjectId => $data)
        {
            if ($data->ocr === 'error')
            {
                $csv[] = ['id' => $subjectId, 'message' => implode(' -- ', $data->messages), 'url' => $data->url];
                continue;
            }

            $subject = $this->subjectContract->find($subjectId);
            if ($subject === null)
            {
                $csv[] = ['id' => $subjectId, 'message' => 'Could not locate associated subject in database', 'url' => ''];
                continue;
            }

            $subject->ocr = $data->ocr;
            $this->subjectContract->update($subject->toArray(), $subject->_id);
        }

        return $csv;
    }
}