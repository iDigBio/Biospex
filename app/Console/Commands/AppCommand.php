<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\ExportQueue;
use App\Repositories\Interfaces\Group;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $group;

    /**
     * @var \App\Repositories\Interfaces\ExportQueue
     */
    private $queue;

    /**
     * AppCommand constructor.
     */
    public function __construct(Group $group, ExportQueue $queue) {
        parent::__construct();
        $this->group = $group;
        $this->queue = $queue;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $result = $this->queue->findByIdExpeditionActor(1,251,2);
        $users = $result->expedition->project->group->users->push($result->expedition->project->group->owner);
        dd($users);

        $group = \App\Models\Group::with('owner')->find(22);
        $result = $this->group->getUserNotifications(22);
        $result->push($group->owner);
        dd($result);
    }
}