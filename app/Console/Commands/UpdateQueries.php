<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Workflow;
use Illuminate\Console\Command;


class UpdateQueries extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(Workflow $workflow)
    {
        parent::__construct();
        
        $this->workflow = $workflow;
    }

    /**
     * handle
     */
    public function handle()
    {
        $workflows = $this->workflow->skipCache()->all();
        foreach ($workflows as $workflow)
        {
            $this->workflow->update(['enabled' => 1], $workflow->id);
        }
    }
}