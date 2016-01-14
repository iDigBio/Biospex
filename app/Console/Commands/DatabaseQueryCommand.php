<?php namespace Console\Commands;

use Illuminate\Console\Command;


class DatabaseQueryCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'db:query';

    /**
     * The console command description.
     */
    protected $description = 'Used to run queries on the database';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire queue.
     *
     * @param Mailer $mailer
     * @param Config $config
     */
    public function fire()
    {

        return;
    }
}
