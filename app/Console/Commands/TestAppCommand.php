<?php

namespace App\Console\Commands;

use App\Jobs\BuildOcrBatches;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Subject;
use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var BuildOcrBatches
     */
    private $batches;
    /**
     * @var Project
     */
    private $project;
    /**
     * @var Subject
     */
    private $subject;

    use DispatchesJobs;
    
    /**
     * TestAppCommand constructor.
     */
    public function __construct(Project $project, Subject $subject)
    {
        parent::__construct();
        
        $this->project = $project;
        $this->subject = $subject;
    }

    /**
     * handle
     */
    public function handle()
    {
        $expeditionIds = [1,2,3,4,5,6,7,8,9,10,11];

        $subjects = $this->subject->findByProjectId(6);
        foreach ($subjects as $subject)
        {
            $array = [];
            foreach ($subject->expedition_ids as $value)
            {
                $array = [];
                if (in_array((int) $value, $expeditionIds, true))
                {
                    $array[] = $value;
                }
                else
                {
                    $array[] = 5;
                }
            }
            
            $subject->expedition_ids = $array;
            $subject->save();
        }


        /*
        $project = $this->project->findWith(8, ['group.permissions', 'workflow.actors']);
        $batches = new BuildOcrBatches($project, 6);
        $batches->handle();
        */
        /* Empty ocr values 
        $subjects = $this->subject->findByProjectId(6);
        foreach($subjects as $subject)
        {
            $subject->ocr = '';
            $subject->save();
        }
        return;
        */

    }
}
