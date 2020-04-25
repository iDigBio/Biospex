<?php

namespace App\Console\Commands;

use App\Services\MongoDbService;
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
     * @var \App\Services\MongoDbService
     */
    private $service;

    /**
     * AppCommand constructor.
     */
    public function __construct(MongoDbService $service) {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $query = [
            'project_id' => 26,
            'ocr'        => '',
        ];

        $this->service->setCollection('subjects');
        $results = $this->service->count($query);
        dd($results);
    }
}