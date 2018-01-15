<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\Subject;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use MongoCollection;

class ClearOcrResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:ocr {projectId} {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear project expeditions ocr values.';
    
    /**
     * @var Subject
     */
    private $subjectContract;

    /**
     * Create a new command instance.
     *
     * @param Subject $subjectContract
     */
    public function __construct(Subject $subjectContract)
    {
        parent::__construct();

        $this->subjectContract = $subjectContract;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projectId = $this->argument('projectId');
        $expeditionId = $this->argument('expeditionId');

        $collection = $this->setCollection($projectId, $expeditionId);
        $query = $expeditionId === null ?
            ['project_id' => (int) $projectId] :
            ['project_id' => (int) $projectId, 'expedition_ids' => (int) $expeditionId];
        $result = $collection->find($query);

        foreach ($result as $doc)
        {
            $doc['ocr'] = '';
            $collection->update(['_id' => (string) $doc['_id']], $doc);
        }
    }

    /**
     * Query MongoDB and return cursor
     * @return MongoCollection
     */
    protected function setCollection()
    {
        $databaseManager = app(DatabaseManager::class);
        $client = $databaseManager->connection('mongodb')->getMongoClient();
        $collection =$client->{config('database.connections.mongodb.database')}->subjects;

         return $collection;
    }
}
