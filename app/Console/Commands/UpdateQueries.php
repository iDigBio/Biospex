<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsJob;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\NfnClassification;
use App\Repositories\Contracts\NfnWorkflow;
use App\Services\Api\NfnApi;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


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
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * handle
     */
    public function handle(
        NfnApi $nfnApi,
        Expedition $expeditionRepo,
        NfnWorkflow $nfnWorkflowRepo
    )
    {

        $expeditions = $expeditionRepo->skipCache()->has('nfnWorkflow')->get();

        $nfnApi->setProvider();
        foreach ($expeditions as $expedition)
        {
            $this->dispatch((new NfnClassificationsJob($expedition->id, true))->onQueue(Config::get('config.beanstalkd.job')));
        }

        $workflows = $nfnWorkflowRepo->skipCache()->with(['expedition.stat', 'expedition.actors'])->get();
        foreach ($workflows as $workflow)
        {
            if ((int) $workflow->expedition->stat->transcriptions_completed === 100)
            {
                foreach ($workflow->expedition->actors as $actor)
                {
                    if ($actor->id === 2)
                    {
                        $actor->pivot->queued = 0;
                        ++$actor->pivot->state;
                        $actor->completed = 1;
                        $actor->pivot->save();
                    }
                }
            }
            else
            {
                foreach ($workflow->expedition->actors as $actor)
                {
                    if ($actor->id === 2)
                    {
                        $actor->pivot->queued = 0;
                        $actor->pivot->state = 1;
                        $actor->completed = 0;
                        $actor->error = 0;
                        $actor->pivot->save();
                    }
                }
            }

        }
    }
}