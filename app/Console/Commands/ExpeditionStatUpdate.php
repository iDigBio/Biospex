<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Interfaces\Expedition;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ExpeditionStatUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Expedition Stats by setting ExpeditionStatJob. Takes comma separated expedition ids or blank.';

    /**
     * @var
     */
    private $expeditionIds;


    /**
     * Execute command
     * @param Expedition $expeditionContract
     */
    public function handle(Expedition $expeditionContract)
    {
        $this->expeditionIds = null ===  $this->argument('ids') ?
            null :
            explode(',', $this->argument('ids'));

        $expeditions = $expeditionContract->getExpeditionStats($this->expeditionIds);

        $this->setJobs($expeditions);
    }

    /**
     * Loop stats for setting jobs.
     *
     * @param Collection $expeditions
     */
    private function setJobs($expeditions)
    {
        $projectIds = $expeditions->map(function ($expedition){
            ExpeditionStatJob::dispatch($expedition->id);

            return $expedition->project_id;
        });

        $projectIds->unique()->values()->each(function ($projectId){
            AmChartJob::dispatch($projectId);
        });
    }
}
