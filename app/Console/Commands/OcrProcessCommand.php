<?php

namespace App\Console\Commands;

use App\Interfaces\OcrCsv;
use App\Interfaces\OcrQueue;
use App\Notifications\OcrProcessComplete;
use App\Services\Process\OcrRequest;
use Illuminate\Console\Command;
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
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * @var OcrCsv
     */
    private $ocrCsvContract;

    /**
     * OcrProcessCommand constructor.
     *
     * @param OcrRequest $ocrRequest
     * @param OcrQueue $ocrQueueContract
     * @param OcrCsv $ocrCsvContract
     */
    public function __construct(
        OcrRequest $ocrRequest,
        OcrQueue $ocrQueueContract,
        OcrCsv $ocrCsvContract
    )
    {
        parent::__construct();

        $this->ocrRequest = $ocrRequest;
        $this->ocrQueueContract = $ocrQueueContract;
        $this->ocrCsvContract = $ocrCsvContract;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $record = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($record === null)
        {
            return;
        }

        try
        {
            $this->processRecord($record);
        }
        catch (\Exception $e)
        {
            $record->error = 1;
            $this->ocrQueueContract->update($record->toArray(), $record->id);

            return;
        }

        Artisan::call('ocr:poll');
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @throws \Exception
     */
    private function processRecord($record)
    {
        if ( ! $record->status)
        {
            $this->ocrRequest->sendOcrFile($record);

            $record->status = 1;
            $this->ocrQueueContract->update($record->toArray(), $record->id);

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
            throw new \Exception(trans('errors.ocr_file_error',
                ['title' => $record->title, 'id' => $record->id, 'message' => 'Json file header returned status error.']));
        }

        $this->updateProcessed($record, $file);

        $csv = $this->ocrRequest->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($record, $csv);

        $record->status = 2;
        $this->ocrQueueContract->update($record->toArray(), $record->id);

        if ($record->batch)
        {
            $this->sendNotify($record);
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
     * Send notification for completed ocr process
     *
     * @param $record
     * @throws \Exception
     */
    private function sendNotify($record)
    {
        $csv = create_csv(json_decode($record->ocrCsv->subjects, true));
        $record->group->owner->notify(new OcrProcessComplete($record->project->title, $csv));
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
        $this->ocrQueueContract->update($record->toArray(), $record->id);
    }
}
