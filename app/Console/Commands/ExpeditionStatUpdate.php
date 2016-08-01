<?php

namespace App\Console\Commands;

use App\Jobs\ExpeditionStatJob;
use App\Repositories\Contracts\ExpeditionStat;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class ExpeditionStatUpdate extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:update {expedition?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Expedition Stats by setting ExpeditionStatJob. Pass in Expedition Id or run all.';


    /**
     * Execute command
     */
    public function handle()
    {
        $stats = $this->findStats();

        $this->setJobs($stats);

        Artisan::call('amchart:update');
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
            $repo->skipCache()->with(['expedition.project'])->where(['expedition_id' => $expeditionId])->get();

    }

    /**
     * Loop stats for setting jobs.
     *
     * @param array $stats
     */
    private function setJobs($stats)
    {
        foreach ($stats as $stat)
        {
            $this->setJob($stat->expedition->project->id, $stat->expedition->id);
        }
    }

    /**
     * Create job for the expedition.
     *
     * @param $projectId
     * @param $expeditionId
     */
    protected function setJob($projectId, $expeditionId)
    {
        $this->dispatch((new ExpeditionStatJob($projectId, $expeditionId))->onQueue(Config::get('config.beanstalkd.job')));
    }
}
