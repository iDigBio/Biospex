<?php

namespace App\Console\Commands;

use App\Jobs\PanoptesProjectUpdateJob;
use App\Repositories\Interfaces\PanoptesProject;
use Illuminate\Console\Command;

class PanoptesProjectUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panoptes:project {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expedition panoptes_projects. Accepts comma separated ids or empty.';

    /**
     * @var
     */
    private $expeditionIds;

    /**
     * PanoptesProjectUpdate constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \App\Repositories\Interfaces\PanoptesProject $panoptesProject
     */
    public function handle(PanoptesProject $panoptesProject)
    {
        $this->setIds();

        $projects = $this->expeditionIds === null ?
            $panoptesProject->all() :
            $panoptesProject->whereIn('expedition_id', $this->expeditionIds);

        $projects->each(function($project){
            PanoptesProjectUpdateJob::dispatch($project);
        });
    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = null ===  $this->argument('expeditionIds') ? null :
            explode(',', $this->argument('expeditionIds'));
    }
}
