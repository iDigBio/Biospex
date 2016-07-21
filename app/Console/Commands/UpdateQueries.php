<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Workflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class UpdateQueries extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'update:queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * handle
     */
    public function handle()
    {
        DB::statement("update ltm_translations set ltm_translations.group = 'html' where ltm_translations.group = 'welcome'");
    }
}