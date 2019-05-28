<?php

namespace App\Console\Commands;

use App\Jobs\TranscriptLocationUpdate;
use App\Models\Project;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * AppCommand constructor.
     * "13,15,16,17,18,26,31,33,34,36,38,44,45,47,49,51,53,55,58,59,61,62,63,65,66,75,77,78,82"
     * 13,15,16,17,18,26,31,33,34,36,38,44,45,47,49,51,53,55,58,59,61,62,63,65,66,75,77,78,82
     *
     * @param \App\Services\MongoDbService $service
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Repositories\Interfaces\TranscriptionLocation $transcriptionLocation
     * @param \App\Repositories\Interfaces\StateCounty $stateCounty
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //TranscriptLocationUpdate::dispatch(13);

        $projectIds = Project::whereHas('nfnWorkflows')->get()->pluck('id');
        $projectIds->each(function ($projectId){
            TranscriptLocationUpdate::dispatch($projectId);
        });
    }
}