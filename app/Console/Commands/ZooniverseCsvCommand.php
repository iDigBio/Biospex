<?php

namespace App\Console\Commands;

use App\Jobs\ZooniverseCsvJob;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Console\Command;

class ZooniverseCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:csv {expeditionIds?*} {--delayed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start process for csv creation from Zooniverse.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @return void
     */
    public function handle(Expedition $expeditionContract)
    {
        $expeditionIds = empty($this->argument('expeditionIds')) ?
            $this->getExpeditionIds($expeditionContract) : $this->argument('expeditionIds');

        ZooniverseCsvJob::dispatch($expeditionIds, $this->option('delayed'));
    }

    /**
     * Get all expeditions for process if no ids are passed.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @return array
     */
    private function getExpeditionIds(Expedition $expeditionContract): array
    {
        $expeditions = $expeditionContract->getExpeditionsForZooniverseProcess();

        return $expeditions->pluck('id')->toArray();
    }
}
