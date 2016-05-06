<?php

namespace App\Console\Commands;

use App\Jobs\BuildOcrBatches;
use App\Repositories\Contracts\Project;
use Illuminate\Console\Command;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

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

    /**
     * @var BuildOcrBatches
     */
    private $batches;
    /**
     * @var Project
     */
    private $project;

    use DispatchesJobs;
    
    /**
     * TestAppCommand constructor.
     */
    public function __construct(Project $project)
    {
        parent::__construct();
        
        $this->project = $project;
    }

    /**
     * Fire
     */
    public function fire()
    {
        
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
