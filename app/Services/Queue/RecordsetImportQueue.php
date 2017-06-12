<?php

namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\ProjectContract;
use App\Services\Process\RecordSet;
use App\Services\Report\Report;
use App\Exceptions\Handler;

class RecordSetImportQueue extends QueueAbstract
{

    /**
     * @var RecordSet
     */
    public $record;

    /**
     * @var Report
     */
    public $report;

    /**
     * @var ProjectContract
     */
    protected $projectContract;

    /**
     * @var Handler
     */
    protected $handler;


    /**
     * RecordSetImportQueue constructor.
     *
     * @param RecordSet $record
     * @param Report $report
     * @param ProjectContract $projectContract
     * @param Handler $handler
     */
    public function __construct(
        RecordSet $record,
        Report $report,
        ProjectContract $projectContract,
        Handler $handler
    )
    {
        $this->record = $record;
        $this->report = $report;
        $this->projectContract = $projectContract;
        $this->handler = $handler;
    }

    /**
     * Fire method
     *
     * @param $job
     * @param $data
     * @throws BiospexException
     */
    public function fire($job, $data)
    {
        $this->job = $job;
        $this->data = $data;

        try
        {
            $this->record->process($data);
            $this->delete();
        }
        catch (BiospexException $e)
        {
            $project = $this->projectContract->with('group.owner')
                ->find($data['project_id']);

            $this->report->addError(trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]));

            $this->report->reportError($project->group->owner->email);

            $this->handler->report($e);
        }
    }
}
