<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;
use App\Services\Model\ExpeditionService;
use App\Services\Model\NfnWorkflowService;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire
     */
    public function fire(ExpeditionService $expeditionService, NfnWorkflowService $nfnWorkflowService)
    {
        $record = $expeditionService->repository->with(['nfnWorkflow', 'subjects'])->find(16);
        dd($nfnWorkflowService->checkNfnWorkflowsEmpty(collect($record->nfnWorkflow)));
    }
}
