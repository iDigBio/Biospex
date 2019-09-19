<?php

namespace App\Console\Commands;

use App\Models\NfnWorkflow;
use App\Models\PanoptesProject;
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
    public function handle()
    {
        $this->updatePanoptes();
    }

    private function updatePanoptes()
    {
        $workflows = NfnWorkflow::all();
        $workflows->each(function ($workflow) {
            $values = [
                'project_id'           => $workflow->project_id,
                'expedition_id'        => $workflow->expedition_id,
                'panoptes_project_id'  => $workflow->project,
                'panoptes_workflow_id' => $workflow->workflow,
                'subject_sets'         => $workflow->subject_sets,
                'slug'                 => $workflow->slug,
            ];

            PanoptesProject::create($values);
        });
    }
}