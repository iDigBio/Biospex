<?php

namespace App\Console\Commands;

use App\Models\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateQueries extends Command
{

    use DispatchesJobs;

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
     * Fire command
     */
    public function handle()
    {
        $expeditions = Expedition::with('stat')->get();
        $expeditions->each(function($expedition){
            if ($expedition->stat !== null) {
                $expedition->stat->local_subject_count = $expedition->subjectCount;
                $expedition->stat->save();
            }
        });
    }

}