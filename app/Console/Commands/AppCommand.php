<?php

namespace App\Console\Commands;


use App\Repositories\Interfaces\User;
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
     * @var \App\Repositories\Interfaces\User
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $result = $this->user->findBy('email', 'cameron_65@yahoo.com');
        dd($result);
    }
}
