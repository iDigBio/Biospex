<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\NfnClassification;
use App\Services\Api\NfnApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class UpdateQueries extends Command
{

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
    public function handle(NfnApi $nfnApi, Expedition $expeditionRepo, NfnClassification $classificationRepo)
    {
        $expeditions = $expeditionRepo->skipCache()->whereNotNull('nfn_workflow_id')->get();

        $nfnApi->setProvider();
        foreach ($expeditions as $expedition)
        {
            $classifications = $classificationRepo->where(['project_id' => $expedition->project_id, 'expedition_id' => $expedition->id])->get();
            $result = json_decode($nfnApi->getWorkflow($expedition->nfn_workflow_id), true);

            $attributes = [
                'project_id'    => $expedition->project->id,
                'expedition_id' => $expedition->id
            ];

            $workflow = $result['workflows'][0];

            $values = [
                'project_id'      => $expedition->project->id,
                'project'      => $workflow['links']['project'],
                'workflow'     => $workflow['id'],
                'subject_sets' => isset($workflow['links']['subject_sets']) ? $workflow['links']['subject_sets'] : ''
            ];

            $result = $expedition->nfnWorkflow()->updateOrCreate($attributes, $values);

            foreach ($classifications as $classification)
            {
                $classificationRepo->update(['nfn_workflow_id' => $result->id], $classification->id);
            }
        }

        DB::statement('ALTER TABLE nfn_classifications DROP COLUMN `project_id`;');
        DB::statement('ALTER TABLE nfn_classifications DROP COLUMN `expedition_id`;');
        DB::statement('ALTER TABLE expeditions DROP COLUMN `nfn_workflow_id`;');
    }
}