<?php

namespace App\Console\Commands;

use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Interfaces\NfnWorkflow;
use App\Services\Api\NfnApi;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Repositories\Interfaces\NfnWorkflow
     */
    private $nfnWorkflowContract;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(NfnWorkflow $nfnWorkflowContract)
    {
        parent::__construct();
        $this->nfnWorkflowContract = $nfnWorkflowContract;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $nfnWorkflows = $this->nfnWorkflowContract->all();
        $nfnWorkflows->each(function($nfnWorkflow){
            UpdateNfnWorkflowJob::dispatch($nfnWorkflow);
        });
    }
}