<?php

namespace App\Jobs;

use App\Interfaces\User;
use App\Models\OcrQueue as Model;
use App\Notifications\JobError;
use App\Notifications\OcrProcessComplete;
use App\Services\Csv\Csv;
use App\Services\Process\OcrRequest;
use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OcrProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Model
     */
    public $record;

    /**
     * Create a new job instance.
     *
     * @param Model $record
     */
    public function __construct(Model $record)
    {
        //
        $this->record = $record;
        $this->onQueue(config('config.beanstalkd.ocr'));
    }

    /**
     * Execute the job.
     *
     * @param OcrRequest $ocrRequest
     * @param Csv $csvService
     * @param User $userContract
     * @return void
     */
    public function handle(
        OcrRequest $ocrRequest,
        Csv $csvService,
        User $userContract
    )
    {
        try
        {
            $this->processRecord($ocrRequest, $csvService);
        }
        catch (\Exception $e)
        {
            $user = $userContract->find(1);
            $this->record->error = 1;
            $this->record->save();

            $messages = [
                $this->record->title,
                'Error processing ocr record ' . $this->record->id,
                'Message: ' . $e->getMessage(),
                'Line: ' . $e->getLine()
            ];

            $user->notify(new JobError(__FILE__, $messages));
        }

        Artisan::call('ocr:poll');
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param OcrRequest $ocrRequest
     * @param $csvService
     * @throws \Exception
     */
    private function processRecord($ocrRequest, $csvService)
    {
        if ( ! $this->record->status)
        {
            $ocrRequest->sendOcrFile($this->record);

            $this->record->status = 1;
            $this->record->save();

            return;
        }

        $file = $ocrRequest->requestOcrFile($this->record->uuid);

        if ( ! $ocrRequest->checkOcrFileHeaderExists($file))
        {
            return;
        }


        if ($ocrRequest->checkOcrFileInProgress($file))
        {
            $this->updateProcessed($file);

            return;
        }

        if ($ocrRequest->checkOcrFileError($file))
        {
            throw new \Exception(trans('errors.ocr_file_error', [
                'title' => $this->record->title,
                'id' => $this->record->id,
                'message' => 'Json file header returned status error.'
            ]));
        }

        $this->updateProcessed($file);

        $csv = $ocrRequest->updateSubjectsFromOcrFile($file);

        $this->setOcrCsv($csv);

        $this->record->status = 2;
        $this->record->save();

        if ($this->record->batch)
        {
            $this->sendNotify($csvService);
            $this->record->ocrCsv->delete();
        }

        $ocrRequest->deleteJsonFiles([$this->record->uuid . '.json']);
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $csv
     */
    private function setOcrCsv($csv)
    {
        $subjects = $this->record->ocrCsv->subjects === '' ? [] : $this->record->ocrCsv->subjects;
        $this->record->ocrCsv->subjects = $subjects === null ? $csv : array_merge($subjects, $csv);
        $this->record->ocrCsv->save();
    }

    /**
     * Send notification for completed ocr process
     *
     * @param Csv $csvService
     * @throws \Exception
     */
    private function sendNotify($csvService)
    {
        $csv = null;
        if ( ! empty($this->record->ocrCsv->subjects))
        {
            $csv = config('config.export_reports_dir') . '/' . str_random() . '.csv';
            $csvService->writerCreateFromPath($csv);
            $headers = array_keys($this->record->ocrCsv->subjects[0]);
            $csvService->insertOne($headers);
            $csvService->insertAll($this->record->ocrCsv->subjects);
        }

        $this->record->project->group->owner->notify(new OcrProcessComplete($this->record->project->title, $csv));
    }

    /**
     * Update subjects processed.
     *
     * @param $file
     */
    private function updateProcessed($file)
    {
        $this->record->processed = (int) $file->header->complete;
        $this->record->save();
    }
}
