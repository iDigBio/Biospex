<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use Illuminate\Console\Command;


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
        $workflows = [
            17 => 2046,
            18 => 2050,
            20 => 2078,
            21 => 2079,
            24 => 2153,
            26 => 2313,
            27 => 2343,
            29 => 2249,
            30 => 2255,
            31 => 2256
        ];

        $expeditions = Expedition::whereIn('id', [17,18,20,21,24,26,27,29,30,31])->withTrashed()->get();

        foreach ($expeditions as $expedition)
        {
            echo $expedition->id . PHP_EOL;
            $expedition->nfn_workflow_id = $workflows[$expedition->id];
            $expedition->save();
        }
    }
}