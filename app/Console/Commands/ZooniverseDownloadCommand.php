<?php

namespace App\Console\Commands;

use App\Jobs\ZooniverseCsvDownloadJob;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Console\Command;

class ZooniverseDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:download {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends request for Zooniverse csv download if ';

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
     * @param \App\Services\Csv\ZooniverseCsvService $service
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(ZooniverseCsvService $service)
    {
        try {
            $expeditionId = $this->argument('expeditionId');

            $uri = $service->checkCsvRequest($expeditionId);
            if (! isset($uri)) {
                throw new \Exception(t('Uri is not available at this time.'));
            }

            ZooniverseCsvDownloadJob::dispatch($expeditionId, $uri);
        }
        catch (\Exception $e)
        {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
