<?php

namespace App\Console\Commands;

use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Contracts\NfnWorkflowContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NfnWorkflowUpdate extends Command
{
    use DispatchesJobs;

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
    protected $description = 'Update Expedtion NfN Workflows. Accepts comma separated ids or empty.';

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
     * @param NfnWorkflowContract $nfnWorkflow
     * @return mixed
     */
    public function handle(NfnWorkflowContract $nfnWorkflow)
    {
        $this->setIds();

        $workflows = $this->getWorkflows($nfnWorkflow);

        foreach ($workflows as $workflow)
        {
            $this->dispatch((new UpdateNfnWorkflowJob($workflow))->onQueue(config('config.beanstalkd.classification')));
        }

    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = null ===  $this->argument('expeditionIds') ? null : explode(',', $this->argument('expeditionIds'));
    }

    /**
     * Retrieve workflows.
     *
     * @param NfnWorkflowContract $nfnWorkflow
     * @return mixed
     */
    private function getWorkflows(NfnWorkflowContract $nfnWorkflow)
    {
        return null === $this->expeditionIds ?
            $nfnWorkflow->setCacheLifetime(0)->findAll() :
            $nfnWorkflow->setCacheLifetime(0)->findWhereIn(['expedition_id', [$this->expeditionIds]]);
    }
}
