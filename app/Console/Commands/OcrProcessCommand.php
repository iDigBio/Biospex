<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\OcrRequest;
use App\Services\Report\OcrReport;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Command;

class OcrProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrprocess:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls Ocr server for file status and fires polling event';
    
    /**
     * @var OcrRequest
     */
    private $ocrRequest;

    /**
     * @var OcrQueue
     */
    private $ocrQueue;

    /**
     * @var OcrReport
     */
    private $ocrReport;

    /**
     * @var OcrCsv
     */
    private $ocrCsv;
    
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * OcrProcessCommand constructor.
     *
     * @param OcrRequest $ocrRequest
     * @param OcrQueue $ocrQueue
     * @param OcrReport $ocrReport
     * @param OcrCsv $ocrCsv
     * @param Dispatcher $dispatcher
     */
    public function __construct(
        OcrRequest $ocrRequest,
        OcrQueue $ocrQueue,
        OcrReport $ocrReport,
        OcrCsv $ocrCsv,
        Dispatcher $dispatcher
    )
    {
        parent::__construct();

        $this->ocrRequest = $ocrRequest;
        $this->ocrQueue = $ocrQueue;
        $this->ocrReport = $ocrReport;
        $this->ocrCsv = $ocrCsv;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $record = $this->ocrQueue->findFirstWith(['project.group.owner', 'ocrCsv']);
        if ($record->isEmpty())
        {
            return;
        }

        try
        {
            $this->processRecord($record);
        }
        catch (\Exception $e)
        {
            $this->ocrQueue->updateOcrError($record->ocr_csv_id);
            $this->addReportError($record->id, $e->getMessage() . ': ' . $e->getTraceAsString());
            $this->ocrReport->reportSimpleError($record->project->group->id);
        }

        $this->dispatcher->fire(new PollOcrEvent($this->ocrQueue));
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @return string|void
     * @throws \Exception
     */
    private function processRecord($record)
    {
        if ($record === null)
        {
            return;
        }
        
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
            $this->updateSubjectRemaining($record, $file);
            
            return;
        }

        if ($this->ocrRequest->checkOcrFileError($file))
        {            
            $record->error = 1;
            $record->save();
            $this->addReportError($record->id, trans('emails.error_ocr_header'));
            $this->ocrReport->reportSimpleError($record->project->group->id);

            return;
        }

        $this->updateSubjectRemaining($record, $file);

        $csv = $this->ocrRequest->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($record, $csv);

        $record->status = 2;
        $record->save();

        if ($record->batch)
        {
            $this->sendReport($record);
            $this->ocrCsv->destroy($record->ocrCsv->id);
        }

        $this->ocrRequest->deleteJsonFiles([$record->uuid . '.json']);
    }

    /**
     * Add error to report.
     *
     * @param $id
     * @param $messages
     * @param string $url
     */
    private function addReportError($id, $messages, $url = '')
    {
        $this->ocrReport->addError(trans('emails.error_ocr_queue',
            [
                'id'      => $id,
                'message' => $messages,
                'url'     => $url,
            ]));
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $csv
     * @param $record
     */
    private function setOcrCsv(&$record, $csv)
    {
        $subjects = $record->ocrCsv->subjects === '' ? [] : json_decode($record->ocrCsv->subjects, true);
        $csv = $subjects === null ? $csv : array_merge($subjects, $csv);
        $record->ocrCsv->subjects = json_encode($csv);
        $record->ocrCsv->save();
    }

    /**
     * Send report for completed ocr process
     *
     * @param $record
     */
    private function sendReport($record)
    {
        $email = $record->project->group->owner->email;
        $title = $record->project->title;
        $this->ocrReport->complete($email, $title, json_decode($record->ocrCsv->subjects, true));
    }

    /**
     * Update subject remaining for record.
     *
     * @param $record
     * @param $file
     */
    private function updateSubjectRemaining($record, $file)
    {
        $record->subject_remaining = max(0, $record->subject_count - $file->header->complete);
        $record->save();
    }
}
