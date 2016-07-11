<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Subject;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
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
    private $subject;

    /**
     * Create a new command instance.
     *
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        parent::__construct();

        $this->subject = $subject;
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
     * @return bool
     */
    protected function setCollection($projectId, $expeditionId = null)
    {
        $databaseManager = app(DatabaseManager::class);
        $db = $databaseManager->connection('mongodb')->getMongoClient()->selectDB(Config::get('database.connections.mongodb.database'));

         return new MongoCollection($db, 'subjects');
    }
}
