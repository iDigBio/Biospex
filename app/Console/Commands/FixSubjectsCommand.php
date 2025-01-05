<?php

namespace App\Console\Commands;

use App\Services\MongoDbService;
use Illuminate\Console\Command;

// TODO: Remove once update is done to subjects.
class FixSubjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected MongoDbService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        echo 'Starting...'.PHP_EOL;
        $this->service->setCollection('subjects');
        $this->service->updateMany([], ['$rename' => ['id' => 'imageId']]);
        echo 'Done'.PHP_EOL;
    }
}
