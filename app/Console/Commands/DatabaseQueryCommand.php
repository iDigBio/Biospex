<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        DB::update('UPDATE profiles INNER JOIN users ON users.id = profiles.user_id SET profiles.timezone = users.timezone');
        DB::update('update groups set label = name');
        DB::update('update groups set name = lower(name)');
        DB::delete('delete from groups where name = "users"');

        return;
    }
}
