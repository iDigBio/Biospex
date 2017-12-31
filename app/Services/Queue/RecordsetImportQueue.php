<?php

namespace App\Services\Queue;

use App\Interfaces\Project;
use App\Notifications\DarwinCoreImportError;
use App\Services\Process\RecordSet;

class RecordSetImportQueue extends QueueAbstract
{

    /**
     * @var RecordSet
     */
    public $record;

    /**
     * @var Project
     */
    protected $projectContract;

    /**
     * RecordSetImportQueue constructor.
     *
     * @param RecordSet $record
     * @param Project $projectContract
     */
    public function __construct(
        RecordSet $record,
        Project $projectContract
    )
    {
        $this->record = $record;
        $this->projectContract = $projectContract;
    }

    /**
     * Fire method
     *
     * @param $job
     * @param $data
     * @throws \Exception
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
        catch (\Exception $e)
        {
            $project = $this->projectContract->findWith($data['project_id'],['group.owner']);

            $message = trans('errors.import_process', [
                'title'   => $project->title,
                'id'      => $project->id,
                'message' => $e->getMessage()
            ]);
            
            $project->group->owner->notify(new DarwinCoreImportError($message, __FILE__));
        }
    }
}
