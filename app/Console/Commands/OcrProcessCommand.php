<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Exceptions\BiospexException;
use App\Exceptions\RequestException;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\OcrRequest;
use App\Services\Report\OcrReport;
use Illuminate\Events\Dispatcher;
use Illuminate\Console\Command;
use App\Exceptions\Handler;

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
     * @var Handler
     */
    private $handler;

    /**
     * OcrProcessCommand constructor.
     *
     * @param OcrRequest $ocrRequest
     * @param OcrQueue $ocrQueue
     * @param OcrReport $ocrReport
     * @param OcrCsv $ocrCsv
     * @param Dispatcher $dispatcher
     * @param Handler $handler
     */
    public function __construct(
        OcrRequest $ocrRequest,
        OcrQueue $ocrQueue,
        OcrReport $ocrReport,
        OcrCsv $ocrCsv,
        Dispatcher $dispatcher,
        Handler $handler
    )
    {
        parent::__construct();

        $this->ocrRequest = $ocrRequest;
        $this->ocrQueue = $ocrQueue;
        $this->ocrReport = $ocrReport;
        $this->ocrCsv = $ocrCsv;
        $this->dispatcher = $dispatcher;
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @throws BiospexException
     */
    public function handle()
    {
        $record = $this->ocrQueue->skipCache()->with(['project.group.owner', 'ocrCsv'])->where([['status', '<=', 1], 'error' => 0])->orderBy(['id' => 'asc'])->first();

        if ($record === null)
        {
            return;
        }

        try
        {
            $this->processRecord($record);
        }
        catch (BiospexException $e)
        {
            $record->error = 1;
            $this->ocrQueue->update($record->toArray(), $record->id);

            $this->handler->report($e);

            return;
        }

        $this->dispatcher->fire(new PollOcrEvent());
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @throws RequestException
     */
    private function processRecord($record)
    {
        if ( ! $record->status)
        {
            $this->ocrRequest->sendOcrFile($record);

            $record->status = 1;
            $this->ocrQueue->update($record->toArray(), $record->id);

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
            throw new RequestException(trans('errors.ocr_file_error',
                ['title' => $record->title, 'id' => $record->id, 'message' => 'Json file header returned status error.']));
        }

        $this->updateSubjectRemaining($record, $file);

        $csv = $this->ocrRequest->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($record, $csv);

        $record->status = 2;
        $this->ocrQueue->update($record->toArray(), $record->id);

        if ($record->batch)
        {
            $this->sendReport($record);
            $this->ocrCsv->delete($record->ocrCsv->id);
        }

        $this->ocrRequest->deleteJsonFiles([$record->uuid . '.json']);

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
        $this->ocrQueue->update($record->toArray(), $record->id);
    }
}
