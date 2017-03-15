<?php

namespace App\Console\Commands;

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
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function fire()
    {

        $expeditions = \App\Models\Expedition::onlyTrashed()->get();
        foreach ($expeditions as $expedition)
        {
            $workflowManager = \App\Models\WorkflowManager::where('expedition_id', '=', $expedition->id)->first();
            if (null === $workflowManager)
            {
                continue;
            }

            $workflowManager->deleted_at = $expedition->deleted_at;
            $workflowManager->created_at = $expedition->created_at;
            $workflowManager->save();

        }
    }
}