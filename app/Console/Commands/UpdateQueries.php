<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\Workflow;
use Illuminate\Support\Facades\DB;

class UpdateQueries extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';
    /**
     * @var Actor
     */
    private $actor;
    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * UpdateQueries constructor.
     * @param Actor $actor
     * @param Workflow $workflow
     */
    public function __construct(Actor $actor, Workflow $workflow)
    {
        parent::__construct();
        $this->actor = $actor;
        $this->workflow = $workflow;
    }

    /**
     * Fire
     */
    public function fire()
    {
        DB::statement("UPDATE actors SET title = 'Notes From Nature Legacy', class = 'NfnLegacy' WHERE actors.id = 1");
        DB::statement("UPDATE actors SET title = 'Notes From Nature Panoptes', class = 'NfnPanoptes' WHERE actors.id = 2");
        DB::statement("UPDATE workflows SET workflow = 'Notes From Nature Legacy' WHERE workflows.id = 2");
        DB::statement("UPDATE workflows SET workflow = 'Notes From Nature Panoptes' WHERE workflows.id = 3");
        DB::statement("UPDATE workflows SET workflow = 'OCR -> Notes From Nature Legacy' WHERE workflows.id = 4");
        DB::statement("UPDATE workflows SET workflow = 'OCR -> Notes From Nature Panoptes' WHERE workflows.id = 5");
    }

}