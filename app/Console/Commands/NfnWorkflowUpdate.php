<?php

namespace App\Console\Commands;

use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Contracts\NfnWorkflow;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

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
     * @param NfnWorkflow $nfnWorkflow
     * @return mixed
     */
    public function handle(NfnWorkflow $nfnWorkflow)
    {
        $this->setIds();

        $workflows = $this->getWorkflows($nfnWorkflow);

        foreach ($workflows as $workflow)
        {
            $this->dispatch((new UpdateNfnWorkflowJob($workflow))->onQueue(Config::get('config.beanstalkd.classification')));
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
     * @param NfnWorkflow $nfnWorkflow
     * @return mixed
     */
    private function getWorkflows(NfnWorkflow $nfnWorkflow)
    {
        return null === $this->expeditionIds ?
            $nfnWorkflow->skipCache()->get() :
            $nfnWorkflow->skipCache()->whereIn('expedition_id', [$this->expeditionIds])->get();
    }
}
