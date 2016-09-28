<?php namespace App\Services\Queue;

use App\Exceptions\BiospexException;
use App\Repositories\Contracts\Project;
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
     * @var Project
     */
    protected $project;

    /**
     * @var Handler
     */
    protected $handler;


    /**
     * RecordSetImportQueue constructor.
     *
     * @param RecordSet $record
     * @param Report $report
     * @param Project $project
     * @param Handler $handler
     */
    public function __construct(RecordSet $record, Report $report, Project $project, Handler $handler)
    {
        $this->record = $record;
        $this->report = $report;
        $this->project = $project;
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
            $project = $this->project->with(['group.owner'])->find($data['project_id']);

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
