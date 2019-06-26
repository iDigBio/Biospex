<?php

namespace App\Console\Commands;

use App\Jobs\TranscriptLocationUpdate;
use App\Models\Download;
use App\Models\Project;
use DB;
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
        $this->updateDownloads();
        $this->updateTranscriptLocations();
    }

    /**
     *
     */
    public function updateDownloads()
    {
        DB::statement("ALTER TABLE `downloads` CHANGE `type` `type` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
        DB::statement("ALTER TABLE `project_resources` CHANGE `type` `type` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");

        $downloads = Download::get();
        $downloads->each(function ($download) {
            switch ($download->type) {
                case 'classifications':
                    $download->type = 'classification';
                    break;
                case 'transcriptions':
                    $download->type = 'transcript';
                    break;
                case 'reconciled':
                    $download->type = 'reconcile';
                    break;
                case 'summary':
                    $download->type = 'summary';
                    break;
                default:
                    break;
            }
            $download->save();
        });
    }

    /**
     * "13,15,16,17,18,26,31,33,34,36,38,44,45,47,49,51,53,55,58,59,61,62,63,65,66,75,77,78,82"
     */
    public function updateTranscriptLocations()
    {
        $project = Project::with([
            'expeditions' => function ($q) {
                $q->whereHas('nfnWorkflow');
            },
        ])->get();

        $project->each(function($project){
            $project->expeditions->each(function($expedition){
                TranscriptLocationUpdate::dispatch($expedition);
            });
        });
    }
}