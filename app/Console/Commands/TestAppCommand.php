<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        return;
    }
}
