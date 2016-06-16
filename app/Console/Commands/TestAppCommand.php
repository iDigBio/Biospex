<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Subject;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
use MongoCollection;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    
    /**
     * BuildAmChartData constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire(Subject $repo)
    {
        $cursor = $this->setCursor();

        foreach ($cursor as $id => $doc)
        {
            $doc['_id'] = $id;
            $repo->update(['ocr' => ''], $id);
            //$repo->update(json_decode(json_encode($subject), true), $id);
        }
    }

    /**
     * Query MongoDB and return cursor
     * @return bool
     */
    protected function setCursor()
    {
        $databaseManager = app(DatabaseManager::class);
        $db = $databaseManager->connection('mongodb')->getMongoClient()->selectDB(Config::get('database.connections.mongodb.database'));

        $collection = new MongoCollection($db, 'subjects');
        $query = ['project_id' => 6];

        return $collection->find($query);
    }
    
}
