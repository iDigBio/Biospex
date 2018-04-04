<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\ExpeditionStat;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\OcrQueue;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\User;
use App\Services\MongoDbService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use File;
use Ramsey\Uuid\Uuid;

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
     * @var \App\Repositories\Interfaces\ExpeditionStat
     */
    private $expeditionStat;

    /**
     * UpdateQueries constructor.
     */
    public function __construct(
        ExpeditionStat $expeditionStat
    )
    {
        parent::__construct();
        $this->expeditionStat = $expeditionStat;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $stats = $this->expeditionStat->all();
        $stats->each(function($stat) {
            $stat->local_subject_count = $stat->subject_count;
            $stat->save();
        });
    }

}