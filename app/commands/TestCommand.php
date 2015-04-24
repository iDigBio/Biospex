<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command {

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
        $tables = DB::select("select table_name from information_schema.tables where table_schema='biospex'");

        foreach ($tables as $table)
        {
            if (Schema::hasColumn($table->table_name, 'created_at'))
            {
                DB::statement("UPDATE {$table->table_name} SET created_at = CONVERT_TZ(created_at, 'America/New_York', 'UTC');");
            }

            if (Schema::hasColumn($table->table_name, 'updated_at'))
            {
                DB::statement("UPDATE {$table->table_name} SET updated_at = CONVERT_TZ(created_at, 'America/New_York', 'UTC');");
        }   }

    }
}
