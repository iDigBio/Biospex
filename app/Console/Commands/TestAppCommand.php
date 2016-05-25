<?php

namespace App\Console\Commands;

use App\Jobs\UpdateExpeditionStat;
use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\Transcription;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;
use MongoDate;


class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var Transcription
     */
    private $transcription;
    /**
     * @var Project
     */
    private $project;

    /**
     * TestAppCommand constructor.
     * @param Transcription $transcription
     * @param Project $project
     */
    public function __construct(Transcription $transcription, Project $project)
    {
        parent::__construct();
        $this->transcription = $transcription;
        $this->project = $project;
    }

    /**
     * handle
     */
    public function handle()
    {
        $project = $this->project->findWith(6, ['expeditions']);
        foreach ($project->expeditions as $expedition)
        {
            
        }
    }
}
