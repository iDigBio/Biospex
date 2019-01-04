<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Storage;

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
     * Create a new job instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $tables = array_map('reset', \DB::select('SHOW TABLES'));
        foreach($tables as $table) {
            echo 'Checking ' . $table . PHP_EOL;
            $sql = null;
            if (Schema::hasColumn($table, 'created_at')) {
                $sql = 'CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP';
            }

            if (Schema::hasColumn($table, 'updated_at')) {
                $sql .= $sql !== null ? ', ' : '';
                $sql .= 'CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP';
            }

            if ($sql !==null) {
                DB::statement("ALTER TABLE `$table` " . $sql . ";");
            }
        }
        echo 'Done' . PHP_EOL;

        //DB::statement("ALTER TABLE projects ADD uuid BINARY(16) NULL AFTER id");
        //DB::statement("ALTER TABLE expeditions ADD uuid BINARY(16) NULL AFTER id");
    }


}
