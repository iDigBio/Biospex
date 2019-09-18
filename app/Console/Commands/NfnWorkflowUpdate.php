<?php

namespace App\Console\Commands;

use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Interfaces\NfnWorkflow;
use Illuminate\Console\Command;

class NfnWorkflowUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfn:workflow {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expedition panoptes workflows. Accepts comma separated ids or empty.';

    /**
     * @var
     */
    private $expeditionIds;

    /**
     * NfnWorkflowUpdate constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param NfnWorkflow $nfnWorkflow
     */
    public function handle(NfnWorkflow $nfnWorkflow)
    {
        $this->setIds();

        $workflows = $this->getWorkflows($nfnWorkflow);

        foreach ($workflows as $workflow)
        {
            UpdateNfnWorkflowJob::dispatch($workflow);
        }

    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = null ===  $this->argument('expeditionIds') ? null :
            explode(',', $this->argument('expeditionIds'));
    }

    /**
     * Retrieve workflows.
     *
     * @param NfnWorkflow $nfnWorkflow
     * @return mixed
     */
    private function getWorkflows(NfnWorkflow $nfnWorkflow)
    {
        return null === $this->expeditionIds ?
            $nfnWorkflow->all() :
            $nfnWorkflow->whereIn('expedition_id', $this->expeditionIds);
    }
}
