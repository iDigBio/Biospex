<?php

namespace App\Console\Commands;

use App\Jobs\NfnClassificationsUpdateJob;
use App\Repositories\Contracts\ExpeditionContract;
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
    protected $signature = 'classifications:update {expeditionIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NfN Classifications for Expeditions. Argument can be comma separated ids or empty.';

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;

    /**
     * Create a new command instance.
     *
     * @param ExpeditionContract $expeditionContract
     */
    public function __construct(ExpeditionContract $expeditionContract)
    {
        parent::__construct();

        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expeditionIds = null ===  $this->argument('expeditionIds') ?
            null :
            explode(',', $this->argument('expeditionIds'));

        $expeditions = null === $expeditionIds ?
            $this->expeditionContract->setCacheLifetime(0)->expeditionsHasRelations(['nfnWorkflow']) :
            $this->expeditionContract->setCacheLifetime(0)->expeditionsHasRelationWhereIn('nfnWorkflow', ['id', [$expeditionIds]]);

        foreach ($expeditions as $expedition)
        {
            $this->dispatch((new NfnClassificationsUpdateJob($expedition->id))
                ->onQueue(Config::get('config.beanstalkd.job')));
        }
    }
}
