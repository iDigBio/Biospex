<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * @var \App\Repositories\Interfaces\User
     */
    private $userContract;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(Group $groupContract, User $userContract)
    {
        parent::__construct();
        $this->groupContract = $groupContract;
        $this->userContract = $userContract;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $user = $this->userContract->find(1);
        $groups = $this->groupContract->all();

        $groups->reject(function ($group) use ($user) {
            echo 'Rejecting ' . $group->id . PHP_EOL;
            return $user->hasGroup($group);
        })->each(function($group) use ($user) {
            echo 'Adding to ' . $group->id . PHP_EOL;
            //$user->assignGroup($group);
        });
    }

}