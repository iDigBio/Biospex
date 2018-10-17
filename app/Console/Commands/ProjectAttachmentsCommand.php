<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Project;
use Illuminate\Console\Command;
use Storage;

class ProjectAttachmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Project $projectContract)
    {
        parent::__construct();
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = $this->projectContract->all();

        $projects->each(function($project){
            if ( ! $this->variantExists($project->logo)) {
                $project->logo->setToBeDeleted();
            }

            if ( ! $this->variantExists($project->banner)) {
                $project->banner->setToBeDeleted();
            }

            $project->save();
        });
    }

    /**
     * @param $attachment
     * @param null $variant
     * @return bool
     */
    public function variantExists($attachment, $variant = null)
    {
        return $attachment->exists() && Storage::disk('public')->exists($attachment->variantPath($variant));
    }
}
