<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\OcrQueue;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use File;
use Ramsey\Uuid\Uuid;

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
     * @var \App\Repositories\Interfaces\User
     */
    private $user;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $queue;

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $group;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expedition;

    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $download;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Repositories\Interfaces\User $user
     * @param \App\Repositories\Interfaces\Project $project
     * @param \App\Repositories\Interfaces\OcrQueue $queue
     * @param \App\Repositories\Interfaces\Group $group
     * @param \App\Repositories\Interfaces\Expedition $expedition
     * @param \App\Repositories\Interfaces\Download $download
     */
    public function __construct(User $user, Project $project, OcrQueue $queue, Group $group, Expedition $expedition, Download $download)
    {
        parent::__construct();
        $this->user = $user;
        $this->project = $project;
        $this->queue = $queue;
        $this->group = $group;
        $this->expedition = $expedition;
        $this->download = $download;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $users = $this->user->all();
        $users->each(function($user){
            if ($user->uuid === null)
            {
                $user->uuid = $this->setUuid();
                $user->save();
            }
        });

        $projects = $this->project->all();
        $projects->each(function($project){
            if ($project->uuid === null)
            {
                $project->uuid = $this->setUuid();
                $project->save();
            }
        });

        $queues = $this->queue->all();
        $queues->each(function($queue){
            if ($queue->uuid === null)
            {
                $queue->uuid = $this->setUuid();
                $queue->save();
            }
        });

        $groups = $this->group->all();
        $groups->each(function($group){
            if ($group->uuid === null)
            {
                $group->uuid = $this->setUuid();
                $group->save();
            }
        });

        $expeditions = $this->expedition->all();
        $expeditions->each(function($expedition){
            if ($expedition->uuid === null)
            {
                $expedition->uuid = $this->setUuid();
                $expedition->save();
            }
        });

        $downloads = $this->download->all();
        $downloads->each(function($download){
            if ($download->uuid === null)
            {
                $download->uuid = $this->setUuid();
                $download->save();
            }
        });
    }

    public function setUuid()
    {
        return Uuid::uuid4()->__toString();
    }
}