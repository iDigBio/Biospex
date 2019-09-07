<?php

namespace App\Console\Commands;

use App\Jobs\PanoptesExportJob;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * AppCommand constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $expedition = 151;
        $workflow = 7955;

        $path = \Storage::path(config('config.nfn_downloads_dir') . '/' . $expedition . '.csv');
        $client_id = "PANOPTES_CLIENT_ID=".config('config.nfnApi.clientId');
        $client_secret = "PANOPTES_CLIENT_SECRET=".config('config.nfnApi.clientSecret');
        shell_exec("$client_id $client_secret sudo panoptes workflow download-classifications $workflow $path");

        /*
        $expeditions = collect([
            151 => 7955, 178 => 8673, 180 => 8676, 207 => 11149, 213 => 11632,
            214 => 11695, 215 => 11705, 216 => 11816
        ]);
        $expeditions->each(function($workflow, $expedition){
            PanoptesExportJob::dispatch($expedition, $workflow);
        });
        */
    }
}