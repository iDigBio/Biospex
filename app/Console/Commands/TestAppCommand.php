<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Property;
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


    private $repo;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(Subject $repo)
    {
        parent::__construct();
        
        $this->repo = $repo;
    }

    /**
     * handle
     */
    public function handle()
    {
        $cursor = $this->setCursor();

        foreach ($cursor as $id => $doc)
        {
            $this->repo->delete($id);
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
        $query = ['project_id' => 13];
        
        return $collection->find($query);
    }
}
