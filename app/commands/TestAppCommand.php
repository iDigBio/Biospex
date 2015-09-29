<?php

use Illuminate\Console\Command;
use Biospex\Repo\WorkflowManager\WorkflowManagerInterface;
use Biospex\Services\Actor\NotesFromNature;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Repo\Subject\SubjectInterface;


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
        ProjectInterface $project,
        SubjectInterface $subject
    )
    {
        parent::__construct();

        $this->subject = $subject;
        $this->project = $project;
        $this->expedition = $expedition;
        $this->workflow = $workflow;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $subjects = $this->subject->all();
        echo "Retrieved subjects" . PHP_EOL;
        foreach($subjects as $subject)
        {
            if (count($subject->expedition_ids) > 0) {
                echo "Looping expeditions" . PHP_EOL;
                $array = [];
                foreach ($subject->expedition_ids as $expedition) {
                    $array[] = (int) $expedition;
                }
                $subject->expedition_ids = $array;
            }

            $subject->save();
        }

        echo "Done" . PHP_EOL;
        /*
        //$this->project->setPass(true);

        Project::with('subjects')->find(1);
        //$project = $this->project->findWith(1, ['subjects']);
        dd(\DB::connection('mongodb')->getQueryLog());
        echo "Done" . PHP_EOL;

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
