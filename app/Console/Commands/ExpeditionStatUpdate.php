<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\ExpeditionStat;
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
     */
    public function handle()
    {
        $this->setIds();

        $stats = $this->findStats();

        $this->setJobs($stats);
    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = null ===  $this->argument('ids') ? null : explode(',', $this->argument('ids'));
    }

    /**
     * Return records from expedition_stats table.
     *
     * @return mixed
     */
    private function findStats()
    {
        $repo = app(ExpeditionStat::class);

        $expeditionId = $this->argument('expedition');

        return null === $expeditionId ?
            $repo->skipCache()->with(['expedition.project'])->get() :
            $repo->skipCache()->with(['expedition.project'])->whereIn('expedition_id', $this->expeditionIds)->get();

    }

    /**
     * Loop stats for setting jobs.
     *
     * @param array $stats
     */
    private function setJobs($stats)
    {
        $projectIds = [];
        foreach ($stats as $stat)
        {
            $projectIds = $stat->expedition->project->id;
            $this->setJob($stat->expedition->project->id, $stat->expedition->id);
        }

        $this->dispatchAmCharts($projectIds);
    }

    /**
     * Create job for the expedition.
     *
     * @param $projectId
     * @param $expeditionId
     */
    private function setJob($projectId, $expeditionId)
    {
        $this->dispatch((new ExpeditionStatJob($projectId, $expeditionId))->onQueue(Config::get('config.beanstalkd.job')));
    }

    /**
     * Call AmChart update for projects.
     *
     * @param $projectIds
     */
    private function dispatchAmCharts($projectIds)
    {
        $projectIds = array_unique($projectIds);

        foreach ($projectIds as $projectId)
        {
            $this->dispatch((new AmChartJob($projectId))->onQueue(Config::get('config.beanstalkd.job')));
        }
    }
}
