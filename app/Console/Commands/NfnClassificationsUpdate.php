<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsJob;
use App\Repositories\Contracts\Expedition;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Config;

class NfnClassificationsUpdate extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classifications:update {ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Arguement can be comma separated ids or empty.';

    /**
     * @var Expedition
     */
    private $expedition;

    /**
     * @var
     */
    private $expeditionIds;

    /**
     * Create a new command instance.
     *
     * @param Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {
        parent::__construct();
        $this->expedition = $expedition;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setIds();

        $expeditions = $this->getExpeditions();

        foreach ($expeditions as $expedition)
        {
            $this->dispatch((new NfnClassificationsJob($expedition->id))->onQueue(Config::get('config.beanstalkd.job')));
        }
    }

    /**
     * Set expedition ids if passed via argument.
     */
    private function setIds()
    {
        $this->expeditionIds = null ===  $this->argument('ids') ? null : explode(',', $this->argument('ids'));
    }

    /**
     * Retrieve expeditions.
     *
     * @return array
     */
    private function getExpeditions()
    {
        return null === $this->expeditionIds ?
            $expeditions = $this->expedition->skipCache()->with(['project'])->whereNotNull('nfn_workflow_id')->get() :
            $expeditions = $this->expedition->skipCache()->with(['project'])->whereIn('id', [$this->expeditionIds])->whereNotNull('nfn_workflow_id')->get();
    }
}
