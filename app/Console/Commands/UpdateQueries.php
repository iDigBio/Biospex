<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\ExpeditionContract;
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
    public function fire(ExpeditionContract $contract)
    {
        $hasRelations = $withRelations = ['nfnActor', 'stat'];
        $records = $contract->setCacheLifetime(0)->findAllHasRelationsWithRelations($hasRelations, $withRelations);

        $records->each(function($record){
            $actor = $record->nfnActor->first();

            if ((int) $record->stat->percent_completed === 100)
            {
                echo 'setting completed ' . $record->id . PHP_EOL;
                $actor->pivot->completed = 1;
                $actor->pivot->queued = 0;
                $actor->pivot->state = 1;
                $actor->pivot->save();
            }
            else
            {
                echo 'setting state ' . $record->id . PHP_EOL;
                $actor->pivot->queued = 0;
                $actor->pivot->state = 1;
                $actor->pivot->save();
            }
        });
    }
}