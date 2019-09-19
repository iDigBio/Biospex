<?php

namespace App\Jobs;

use App\Repositories\Interfaces\Expedition;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationsUpdateJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * NfnClassificationsUpdateJob constructor.
     * @param int $expeditionId
     */
    public function __construct($expeditionId)
    {
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute job.
     *
     * @param Expedition $expeditionContract
     */
    public function handle(Expedition $expeditionContract)
    {
        $expedition = $expeditionContract->getExpeditionsHavingPanoptesProjects($this->expeditionId);

        if ($this->checkIfExpeditionShouldProcess($expedition))
        {
            $this->delete();

            return;
        }

        AmChartJob::dispatch($expedition->project_id);

        $this->delete();
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkIfExpeditionShouldProcess($expedition)
    {
        return null === $expedition
            || ! isset($expedition->panoptesProject)
            || null === $expedition->panoptesProject->panoptes_workflow_id
            || null === $expedition->panoptesProject->panoptes_project_id
            || null === $expedition->nfnActor;
    }
}
