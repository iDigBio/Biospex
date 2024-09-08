<?php

namespace App\Console\Commands;

use App\Jobs\GeoLocateStatsJob;
use App\Models\Expedition;
use Illuminate\Console\Command;

class GeoLocateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:geolocatestats {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run geolocate stats job.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');
        $expedition = Expedition::with('geoLocateActor')->find($expeditionId);

        GeoLocateStatsJob::dispatch($expedition->geoLocateActor, true);
    }
}
