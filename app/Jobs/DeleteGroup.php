<?php

namespace App\Jobs;

use App\Models\Group;
use App\Repositories\Interfaces\Group as GroupContract;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;

class DeleteGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Group
     */
    public $group;

    /**
     * Create a new job instance.
     *
     * @param $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->onQueue(config('config.beanstalkd.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Services\MongoDbService $mongoDbService
     * @return void
     */
    public function handle(GroupContract $groupContract, MongoDbService $mongoDbService)
    {
        $group = $groupContract->findWith($this->group->id, ['projects.expeditions.downloads']);

        $group->projects->each(function ($project) use ($mongoDbService) {
            $project->expeditions->each(function ($expedition) use ($mongoDbService) {
                $expedition->downloads->each(function ($download) {
                    File::delete(config('config.export_dir').'/'.$download->file);
                });

                $mongoDbService->setCollection('panoptes_transcriptions');
                $mongoDbService->deleteMany(['subject_expeditionId' => $expedition->id]);

                $mongoDbService->setCollection('transcriptions');
                $mongoDbService->deleteMany(['expedition_id' => $expedition->id]);

                $expedition->delete();
            });

            $mongoDbService->setCollection('subjects');
            $mongoDbService->deleteMany(['project_id' => $project->id]);
        });

        $group->delete();
    }
}
