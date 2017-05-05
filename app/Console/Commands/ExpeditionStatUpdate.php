<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class ExpeditionStatUpdate extends Command
{

    use DispatchesJobs;

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
     * @param Expedition $expeditionRepo
     */
    public function handle(Expedition $expeditionRepo)
    {
        $this->expeditionIds = null ===  $this->argument('ids') ?
            null :
            explode(',', $this->argument('ids'));

        $expeditions = $this->findStats($expeditionRepo);

        $projectIds = $this->setJobs($expeditions);

        $this->dispatchAmCharts($projectIds);
    }

    /**
     * Return records from expedition_stats table.
     *
     * @param Expedition $expeditionRepo
     * @return mixed
     */
    private function findStats(Expedition $expeditionRepo)
    {
        return null === $this->expeditionIds ?
            $expeditionRepo->skipCache()->with(['project'])->whereHas('stat')->get() :
            $expeditionRepo->skipCache()->with(['project'])->whereHas('stat')->whereIn('id', $this->expeditionIds)->get();

    }

    /**
     * Loop stats for setting jobs.
     *
     * @param array $expeditions
     * @return array
     */
    private function setJobs($expeditions)
    {
        $projectIds = [];
        foreach ($expeditions as $expedition)
        {
            $projectIds[] = $expedition->project_id;
            $this->dispatch((new ExpeditionStatJob($expedition->id))->onQueue(Config::get('config.beanstalkd.stat')));
        }

        return $projectIds;
    }

    /**
     * Call AmChart update for projects.
     *
     * @param $projectIds
     */
    private function dispatchAmCharts($projectIds)
    {
        $projectIds = array_unique($projectIds);
        $this->dispatch((new AmChartJob($projectIds))->onQueue(Config::get('config.beanstalkd.chart')));
    }
}
