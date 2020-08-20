<?php

namespace App\Console\Commands;

use App\Services\MongoDbService;
use Illuminate\Console\Command;

class DbFix001Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:001';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes classification id and sets to integer. Then runs fix:002 for mongodb.';

    /**
     * @var \App\Services\MongoDbService
     */
    private $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MongoDbService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->service->setCollection('pusher_transcriptions');
        $criteria = ['classification_id' => ['$type' => 'string']];
        $cursor = $this->service->find($criteria);

        foreach ($cursor as $doc) {
            $attributes = ['classification_id' => (int) $doc['classification_id']];
            $resourceId = $doc['_id'];
            $this->service->updateOneById($attributes, $resourceId);
        }

        echo 'Completed.' . PHP_EOL;
    }
}
