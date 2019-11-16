<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\TranscriptionChartService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\Interfaces\Project;

class AmChartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var
     */
    protected $projectId;

    /**
     * AmChartJob constructor.
     *
     * @param int $projectId
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->onQueue(config('config.chart_tube'));
    }

    /**
     * Handle job.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\Process\TranscriptionChartService $service
     */
    public function handle(Project $projectContract, TranscriptionChartService $service)
    {
        try {
            $project = $projectContract->getProjectForAmChartJob($this->projectId);

            $service->process($project);

            $this->delete();
        }
        catch (\Exception $e)
        {
            $user = User::find(1);

            $messages = [
                'Project Id: '.$this->projectId,
                'Message:' . $e->getFile() . ': ' . $e->getLine() . ' - ' . $e->getMessage()
            ];

            $user->notify(new JobError(__FILE__, $messages));
        }
    }
}
