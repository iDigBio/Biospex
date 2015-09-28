<?php

use Illuminate\Console\Command;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Biospex\Services\Actor\NotesFromNature;

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
        WorkflowManagerInterface $workflow
    )
    {
        parent::__construct();

        $this->workflow = $workflow;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $manager = $this->workflow->findWith(1, ['expedition.actors']);
        foreach ($manager->expedition->actors as $actor) {
            $class = \App::make(NotesFromNature::class);
            $class->setProperties($actor);
            $class->process();
        }

        return;
    }
}
