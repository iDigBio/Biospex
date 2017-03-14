<?php

namespace App\Jobs;

use App\Exceptions\NfnApiException;
use App\Repositories\Contracts\ExpeditionContract;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class NfnClassificationsJob extends Job implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, DispatchesJobs;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * @var bool
     */
    private $all;

    /**
     * NfnClassificationsJob constructor.
     *
     * @param $expeditionId
     * @param bool $all
     */
    public function __construct($expeditionId, $all = false)
    {
        $this->expeditionId = $expeditionId;
        $this->all = $all;
    }

    /**
     * Execute job.
     *
     * @param ExpeditionContract $expeditionContract
     * @throws NfnApiException
     */
    public function handle(ExpeditionContract $expeditionContract)
    {
        $relations = ['project.amChart', 'nfnWorkflow'];
        $expedition = $expeditionContract->setCacheLifetime(0)->expeditionFindWith($this->expeditionId, $relations);

        if ($this->checkForRequiredInformation($expedition))
        {
            $this->delete();

            return;
        }

        $this->dispatch((new ExpeditionStatJob($expedition->id))->onQueue(Config::get('config.beanstalkd.job')));

        if ( $expedition->project->amChart === null || ! $expedition->project->amChart->queued )
        {
            $this->dispatch((new AmChartJob($expedition->project->id))->onQueue(Config::get('config.beanstalkd.job')));
        }

        $this->delete();

    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkForRequiredInformation($expedition)
    {
        return null === $expedition
            || ! isset($expedition->nfnWorkflow)
            || null === $expedition->nfnWorkflow->workflow
            || null === $expedition->nfnWorkflow->project;
    }
}
