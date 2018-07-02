<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Group;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TestAppCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {id?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $group;

    /**
     * Create a new job instance.
     */
    public function __construct(Group $group)
    {
        parent::__construct();
        $this->group = $group;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $groups = $this->group->getUserGroupIds(1);

        $diff = $groups->diff([8]);
        dd($diff);
    }
}
