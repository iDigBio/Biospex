<?php

namespace App\Jobs;

use App\Models\Project;
use App\Repositories\Interfaces\Project as ProjectContract;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;

class DeleteProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Project
     */
    public $project;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->onQueue(config('config.beanstalkd.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(ProjectContract $projectContract, MongoDbService $mongoDbService)
    {
        $project = $projectContract->findWith($this->project->id, ['expeditions.downloads']);

        $project->expeditions->each(function ($expedition) use ($mongoDbService) {
            $expedition->downloads->each(function ($download){
                File::delete(config('config.export_dir').'/'.$download->file);
            });

            $mongoDbService->setCollection('pusher_transcriptions');
            $mongoDbService->deleteMany(['expedition_uuid' => $expedition->uuid]);

            $expedition->delete();
        });

        $mongoDbService->setCollection('panoptes_transcriptions');
        $mongoDbService->deleteMany(['subject_projectId' => $project->id]);

        $mongoDbService->setCollection('subjects');
        $mongoDbService->deleteMany(['project_id' => $project->id]);

        $project->delete();
    }
}
