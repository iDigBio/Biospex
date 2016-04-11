<?php

namespace App\Console\Commands;

use App\Events\PollEvent;
use App\Events\PollOcrEvent;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use App\Services\Process\OcrRequest;
use App\Services\Report\OcrReport;
use Illuminate\Console\Command;
use App\Repositories\Contracts\Subject;
use Illuminate\Events\Dispatcher;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    private $subject;
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
     * @var Dispatcher
     */
    private $dispatcher;
    
    public $data = [];
    public $channels = [];
    /**
     * @var OcrRequest
     */
    private $ocrRequest;

    /**
     * Constructor
     *
     * @param Subject $subject
     * @param OcrRequest $ocrRequest
     * @param OcrQueue $ocrQueue
     * @param OcrReport $ocrReport
     * @param OcrCsv $ocrCsv
     * @param Dispatcher $dispatcher
     * @internal param Ocr $ocr
     */
    public function __construct(
        Subject $subject,
        OcrRequest $ocrRequest,
        OcrQueue $ocrQueue,
        OcrReport $ocrReport,
        OcrCsv $ocrCsv,
        Dispatcher $dispatcher
    )
    {
        parent::__construct();
        $this->subject = $subject;
        $this->ocr = $ocrRequest;
        $this->ocrQueue = $ocrQueue;
        $this->ocrReport = $ocrReport;
        $this->ocrCsv = $ocrCsv;
        $this->dispatcher = $dispatcher;
        $this->ocrRequest = $ocrRequest;
    }

    /**
     * Fire
     */
    public function fire()
    {

        /* Empty ocr values 
        $subjects = $this->subject->findByProjectId(6);
        foreach($subjects as $subject)
        {
            $subject->ocr = '';
            $subject->save();
        }
        return;
        */

        /*
        $records = $this->ocrQueue->allWith(['project.group.owner', 'ocrCsv']);
        if ($records->isEmpty())
        {
            return;
        }

        $this->dispatcher->fire(new PollOcrEvent($records));
        
        return;
        */

        $records = $this->ocrQueue->allWith(['project.group.owner', 'ocrCsv']);
        if ($records->isEmpty())
        {
            return;
        }
        
        $grouped = $records->groupBy('project_id')->toArray();
        $totalSubjectsAhead = 0;
        $previousKey = null;
        foreach ($grouped as $key => $group)
        {
            if ($previousKey) {
                $totalSubjectsAhead =+ array_sum(array_column($grouped[$previousKey], 'subject_remaining'));
            }

            $previousKey = $key;

            $groupSubjectCount = array_sum(array_column($group, 'subject_count'));
            $groupSubjectRemaining = array_sum(array_column($group, 'subject_remaining'));

            $this->setChannel($group[0]['project']['group']['id']);

            $this->data[] = [
                'groupId' => $group[0]['project']['group']['id'],
                'projectTitle' => $group[0]['project']['title'],
                'totalSubjectsAhead' => $totalSubjectsAhead,
                'groupSubjectRemaining' => $groupSubjectRemaining,
                'groupSubjectCount' => $groupSubjectCount
            ];
        }

        dd($this->data);
        
        return;
        
        
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


        //$this->dispatcher->fire(new PollOcrEvent($records));

        return;
    }

    private function setChannel($groupId)
    {
        if (in_array("channel-" . $groupId, $this->channels))
        {
            return;
        }
        
        $this->channels[] = "channel-" . $groupId;
    }

    /**
     * Process the record and send requests to ocr servers
     *
     * @param $record
     * @throws \Exception
     */
    private function processRecord(&$record)
    {
        if ( ! $record->status)
        {
            $this->ocr->sendOcrFile($record);
            $record->status = 1;
            $record->save();

            return;
        }

        $file = $this->ocr->requestOcrFile($record->uuid . '.json');

        if ( ! $this->ocr->checkOcrFileHeaderExists($file))
        {
            return;
        }

        if ($this->ocr->checkOcrFileInProgress($file))
        {
            $record->subject_remaining = max(0, ($record->subject_count - $file->header->complete));
            $record->save();

            return;
        }

        if ( ! $this->ocr->checkOcrFileError($file))
        {
            $record->error = 1;
            $record->save();
            $this->addReportError($record->id, trans('emails.error_ocr_header'));
            $this->ocrReport->reportSimpleError($record->project->group->id);

            return;
        }

        $csv = $this->ocr->updateSubjectsFromOcrFile($file);

        $this->setCsvAttachmentArray($record, $csv);

        $attachment = $this->sendReport($record, $csv);

        $this->updateOrDestroyRecord($record, $attachment);

        if ($record->batch)
        {
            $this->ocrCsv->destroy($record->ocrCsv->id);
        }

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
     * Add csv array to database if batch needs attachment
     *
     * @param $csv
     * @param $record
     */
    private function setCsvAttachmentArray($record, &$csv)
    {
        $subjects = ! empty($record->ocrCsv->subjects) ? json_decode($record->ocrCsv->subjects, true) : [];
        $csv = is_null($subjects) ? $csv : array_merge($subjects, $csv);
        $record->ocrCsv->subjects = json_encode($csv);
        $record->ocrCsv->save();

        return;
    }

    /**
     * @param $attachment
     * @param $record
     */
    private function updateOrDestroyRecord($record, $attachment)
    {
        if ( ! $attachment)
        {
            $record->destroy($record->id);
        }
        else
        {
            $record->error = 1;
            $record->attachments = json_encode($attachment);
            $record->save();
        }
    }

    /**
     * Send report for completed ocr process
     *
     * @param $record
     * @param $csv
     * @return array|bool
     */
    private function sendReport($record, &$csv)
    {
        if ($record->batch)
        {
            $email = $record->project->group->owner->email;
            $title = $record->project->title;
            $attachment = $this->ocrReport->complete($email, $title, $csv);

            return $attachment;
        }

        return false;
    }
}
