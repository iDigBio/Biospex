<?php

namespace App\Console\Commands;

use App\Jobs\ZooniversePusherJob;
use App\Jobs\ZooniverseReconcileJob;
use App\Jobs\ZooniverseTranscriptionJob;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Console\Command;

class ZooniverseReconcileChainedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:reconcile-chain {expeditionIds?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconciles and process multiple expeditions including transcriptions and pusher.';

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

        foreach ($expeditionIds as $expeditionId) {
            ZooniverseReconcileJob::withChain([
                new ZooniverseTranscriptionJob($expeditionId),
                new ZooniversePusherJob($expeditionId)
            ])->dispatch($expeditionId);
        }
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
