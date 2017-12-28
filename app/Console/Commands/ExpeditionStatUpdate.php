<?php

namespace App\Console\Commands;

use App\Jobs\AmChartJob;
use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
     * @param ExpeditionContract $expeditionContract
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        $this->expeditionIds = null ===  $this->argument('ids') ?
            null :
            explode(',', $this->argument('ids'));

        $expeditions = $this->findStats($expeditionContract);

        $projectIds = $this->setJobs($expeditions);

        $this->dispatchAmCharts($projectIds);
    }

    /**
     * Return records from expedition_stats table.
     *
     * @param ExpeditionContract $expeditionContract
     * @return mixed
     */
    private function findStats(ExpeditionContract $expeditionContract)
    {
        return null === $this->expeditionIds ?
            $expeditionContract->setCacheLifetime(0)
                ->has('stat')
                ->with('project')
                ->findAll() :
            $expeditionContract->setCacheLifetime(0)
                ->has('stat')
                ->with('project')
                ->findWhereIn(['id', [$this->expeditionIds]]);
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
            $this->dispatch((new ExpeditionStatJob($expedition->id))->onQueue(config('config.beanstalkd.stat')));
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
        collect($projectIds)->unique()->each(function ($projectId){
            $this->dispatch((new AmChartJob($projectId))->onQueue(config('config.beanstalkd.chart')));
        });
    }
}
