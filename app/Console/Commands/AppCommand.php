<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Repositories\Interfaces\PanoptesTranscription;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

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
     * @var \App\Console\Commands\DatabaseManager
     */
    private $databaseManager;

    /**
     * @var \App\Repositories\Interfaces\PanoptesTranscription
     */
    private $panoptesTranscription;

    /**
     * Create a new job instance.
     */
    public function __construct(DatabaseManager $databaseManager, PanoptesTranscription $panoptesTranscription)
    {
        parent::__construct();
        $this->databaseManager = $databaseManager;
        $this->panoptesTranscription = $panoptesTranscription;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        /*
        $project = Project::find(13);
        dd($project->transcriber_count);
        exit;
        */

        $transcribers = $this->panoptesTranscription->getTranscriberCount(13);
        dd($transcribers);

        $this->client = $this->databaseManager->connection('mongodb')->getMongoClient();
        $collection = $this->client->biospex_theme->panoptes_transcriptions;
        $query = ['subject_projectId' => 13];
        $results = $collection->count($query);
        dd($results);
        foreach ($results as $result){
            echo $result['_id'];
        }
    }


}
