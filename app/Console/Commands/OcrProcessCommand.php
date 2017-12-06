<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\OcrCsvContract;
use App\Repositories\Contracts\OcrQueueContract;
use App\Services\Process\OcrRequest;
use App\Services\Report\OcrReport;
use Illuminate\Console\Command;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\Artisan;

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
     * @var OcrQueueContract
     */
    private $ocrQueueContract;

    /**
     * @var OcrReport
     */
    private $ocrReport;

    /**
     * @var OcrCsvContract
     */
    private $ocrCsvContract;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * OcrProcessCommand constructor.
     *
     * @param OcrRequest $ocrRequest
     * @param OcrQueueContract $ocrQueueContract
     * @param OcrReport $ocrReport
     * @param OcrCsvContract $ocrCsvContract
     * @param Handler $handler
     */
    public function __construct(
        OcrRequest $ocrRequest,
        OcrQueueContract $ocrQueueContract,
        OcrReport $ocrReport,
        OcrCsvContract $ocrCsvContract,
        Handler $handler
    )
    {
        parent::__construct();

        $this->ocrRequest = $ocrRequest;
        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrReport = $ocrReport;
        $this->ocrCsvContract = $ocrCsvContract;
        $this->handler = $handler;
    }

    /**
     * Execute the console command.
     *
     * @throws BiospexException
     */
    public function handle()
    {
        $record = $this->ocrQueueContract->setCacheLifetime(0)
            ->with(['project.group.owner', 'ocrCsv'])
            ->where('status', '<=', 1)
            ->where('error', '=', 0)
            ->orderBy('id', 'asc')
            ->findFirst();

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
            $this->ocrQueueContract->update($record->id, $record->toArray());

            $this->handler->report($e);

            return;
        }

        Artisan::call('ocr:poll');
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @throws BiospexException
     */
    private function processRecord($record)
    {
        if ( ! $record->status)
        {
            $this->ocrRequest->sendOcrFile($record);

            $record->status = 1;
            $this->ocrQueueContract->update($record->id, $record->toArray());

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
            throw new BiospexException(trans('errors.ocr_file_error',
                ['title' => $record->title, 'id' => $record->id, 'message' => 'Json file header returned status error.']));
        }

        $this->updateProcessed($record, $file);

        $csv = $this->ocrRequest->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($record, $csv);

        $record->status = 2;
        $this->ocrQueueContract->update($record->id, $record->toArray());

        if ($record->batch)
        {
            $this->sendReport($record);
            $this->ocrCsvContract->delete($record->ocrCsv->id);
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
     * Update subjects processed.
     *
     * @param $record
     * @param $file
     */
    private function updateProcessed($record, $file)
    {
        $record->processed = (int) $file->header->complete;
        $this->ocrQueueContract->update($record->id, $record->toArray());
    }
}
