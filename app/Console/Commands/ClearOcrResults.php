<?php

namespace App\Console\Commands;

use App\Services\MongoDbService;
use Illuminate\Console\Command;

class ClearOcrResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:clear {projectId} {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear project expeditions ocr values.';

    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\MongoDbService $mongoDbService
     */
    public function __construct(MongoDbService $mongoDbService)
    {
        parent::__construct();

        $this->mongoDbService = $mongoDbService;
    }

    /**
     * Execute the console command.
     *
     * @throws \MongoCursorException
     */
    public function handle()
    {
        $projectId = $this->argument('projectId');
        $expeditionId = $this->argument('expeditionId');

        $this->mongoDbService->setCollection('subjects');
        $criteria = null === $expeditionId ?
            ['project_id' => (int) $projectId] :
            ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId];
        $attributes = [ '$set' => ['ocr' => '']];

        $this->mongoDbService->updateMany($attributes, $criteria);
    }
}
