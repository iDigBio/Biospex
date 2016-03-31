<?php

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\Ocr;
use App\Services\Report\OcrReport;
use Illuminate\Console\Command;

class OcrProcessPollCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrprocess:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls Ocr server for file status and fires polling event';

    /**
     * @var Ocr
     */
    private $ocr;

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
     * Create a new command instance.
     *
     * @param Ocr $ocr
     * @param OcrQueue $ocrQueue
     * @param OcrReport $ocrReport
     * @param OcrCsv $ocrCsv
     */
    public function __construct(
        Ocr $ocr,
        OcrQueue $ocrQueue,
        OcrReport $ocrReport,
        OcrCsv $ocrCsv
    )
    {
        parent::__construct();

        $this->ocr = $ocr;
        $this->ocrQueue = $ocrQueue;
        $this->ocrReport = $ocrReport;
        $this->ocrCsv = $ocrCsv;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $records = $this->ocrQueue->allWith(['project.group.owner', 'ocrCsv']);
        if ($records->isEmpty())
        {
            return;
        }

        $firstRecord = $records->first();
        $records = $records->each(function ($record, $key) use ($firstRecord)
        {
            if ($record->id == $firstRecord->id)
            {
                try
                {
                    \Log::alert("process");
                    return $this->processRecord($record);
                }
                catch (\Exception $e)
                {
                    $record->error = 1;
                    $record->save();
                    $this->addReportError($record->id, $e->getMessage() . ': ' . $e->getTraceAsString());
                    $this->ocrReport->reportSimpleError($record->project->group->id);
                }
            }

            return;
        });

        $this->dispatcher->fire(new PollOcrEvent($records));
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @return string|void
     * @throws \Exception
     */
    private function processRecord(&$record)
    {
        if ( ! $record->status)
        {
            $this->ocr->sendOcrFile($record);

            $record->status = 1;
            $record->save();
            \Log::alert("status = 1");

            return;
        }

        $file = $this->ocr->requestOcrFile($record->uuid . '.json');
        \Log::alert("get file");

        if ( ! $this->ocr->checkOcrFileHeaderExists($file))
        {
            \Log::alert("header exist");
            return;
        }

        if ($this->ocr->checkOcrFileInProgress($file))
        {
            $this->updateSubjectRemaining($record, $file);
            \Log::alert("file progress");
            return;
        }

        if ( ! $this->ocr->checkOcrFileError($file))
        {
            $record->error = 1;
            $record->save();
            $this->addReportError($record->id, trans('emails.error_ocr_header'));
            $this->ocrReport->reportSimpleError($record->project->group->id);

            \Log::alert("ocr file error");

            return;
        }

        $this->updateSubjectRemaining($record, $file);

        $csv = $this->ocr->updateSubjectsFromOcrFile($file);
        \Log::alert("updated subjects");

        $this->setOcrCsv($record, $csv);
        \Log::alert("set ocr csv");

        $record->status = 2;
        $record->save();
        \Log::alert("save status = 2");

        if ($record->batch)
        {
            $this->sendReport($record);
            $this->ocrCsv->destroy($record->ocrCsv->id);
        }

        $this->ocr->deleteJsonFiles([$record->uuid . ".json"]);
        \Log::alert("delete file");

        return;
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
                'url'     => ! empty($url) ? $url : ''
            ]));

        return;
    }

    /**
     * Update ocr_csv table with error results
     *
     * @param $csv
     * @param $record
     */
    private function setOcrCsv(&$record, $csv)
    {
        $subjects = ! empty($record->ocrCsv->subjects) ? json_decode($record->ocrCsv->subjects, true) : [];
        $csv = is_null($subjects) ? $csv : array_merge($subjects, $csv);
        $record->ocrCsv->subjects = json_encode($csv);
        $record->ocrCsv->save();

        return;
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

        return;
    }

    /**
     * Update subject remaining for record.
     *
     * @param $record
     * @param $file
     */
    private function updateSubjectRemaining(&$record, $file)
    {
        \Log::alert("update remaining count");
        $record->subject_remaining = max(0, ($record->subject_count - $file->header->complete));
        $record->save();

        return;
    }
}
