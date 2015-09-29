<?php

use Illuminate\Console\Command;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Biospex\Services\Actor\NotesFromNature;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Project\ProjectInterface;

class TestAppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    public function __construct(
        WorkflowManagerInterface $workflow,
        ExpeditionInterface $expedition,
        ProjectInterface $project
    )
    {
        parent::__construct();

        $this->project = $project;
        $this->expedition = $expedition;
        $this->workflow = $workflow;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $project = $this->project->findWith(1, ['subjects']);
        dd($project);
        //$expedition = $this->expedition->findWith(4, ['subjects']);
        //dd($expedition->subjects);

        /*
        $manager = $this->workflow->findWith(2, ['expedition.actors']);
        foreach ($manager->expedition->actors as $actor) {
            $class = \App::make(NotesFromNature::class);
            $class->setProperties($actor);
            $class->process();
        }
        */

        return;
    }
}
