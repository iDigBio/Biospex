<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\User;
use Illuminate\Console\Command;

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
     * @var Group
     */
    private $group;
    /**
     * @var User
     */
    private $user;

    /**
     * TestAppCommand constructor.
     * @param Group $group
     * @param User $user
     */
    public function __construct(Group $group, User $user)
    {
        parent::__construct();
        $this->group = $group;
        $this->user = $user;
    }

    public function fire()
    {
        $user = $this->user->find(1);

        $groups = $this->group->all();

        foreach ($groups as $group)
        {
            if ($user->hasGroup($group))
            {
                echo 'Has group . ' . $group->name . PHP_EOL;
            }
            else
            {
                echo 'Assigning group . ' . $group->name . PHP_EOL;
                $user->assignGroup($group);
            }
        }


    }
}
