<?php

namespace App\Jobs;

use App\Repositories\Contracts\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Config;
use App\Events\PollOcrEvent;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use MongoCollection;

class BuildOcrBatches extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var
     */
    private $projectId;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * @var
     */
    private $ocrData;

    /**
     * BuildOcrBatches constructor.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function __construct($projectId, $expeditionId = null)
    {
        $this->projectId = (int) $projectId;
        $this->expeditionId = $expeditionId === null ? null : (int) $expeditionId;
    }

    /**
     * Handle Job.
     *
     * @param OcrQueue $ocrQueueRepo
     * @param OcrCsv $ocrCsvRepo
     */
    public function handle(OcrQueue $ocrQueueRepo, OcrCsv $ocrCsvRepo)
    {        
        if (Config::get('config.ocr_disable'))
        {
            return;
        }

        if ( ! $this->checkOcrActorExists())
        {
            return;
        }
     
        $this->buildOcrSubjectsArray();
     
        $data = $this->getChunkQueueData();
     
        if (count($data) === 0)
        {
            return;
        }

        $lastKey = array_search(end($data), $data, true);
        $ocrCsv = $ocrCsvRepo->create(['subjects' => '']);

        foreach ($data as $key => $chunk)
        {
            $batch = ($key === $lastKey) ? 1 : 0;
            $count = count($chunk);

            $ocrQueueRepo->create([
                'project_id'        => $this->projectId,
                'ocr_csv_id'        => $ocrCsv->id,
                'data'              => json_encode(['subjects' => $chunk]),
                'subject_count'     => $count,
                'subject_remaining' => $count,
                'batch'             => $batch
            ]);
        }

        app(Dispatcher::class)->fire(new PollOcrEvent());
    }


    /**
     * Check if project has ocr actor.
     * 
     * @return bool
     */
    protected function checkOcrActorExists()
    {
        $project = app(Project::class)->skipCache()->with(['workflow.actors'])->find($this->projectId);
        
        foreach ($project->workflow->actors as $actor)
        {
            if (strtolower($actor->title) === 'ocr')
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the ocr subject array
     */
    protected function buildOcrSubjectsArray()
    {
        $collection = $this->setCollection();
        $query = null === $this->expeditionId ?
            ['project_id' => $this->projectId, 'ocr' => ''] :
            ['project_id' => $this->projectId, 'expedition_ids' => $this->expeditionId, 'ocr' => ''];

        $results = $collection->find($query);

        foreach ($results as $doc)
        {
            $this->buildOcrQueueData($doc);
        }
    }

    /**
     * Query MongoDB and return cursor
     * @return bool
     */
    protected function setCollection()
    {
        $databaseManager = app(DatabaseManager::class);
        $db = $databaseManager->connection('mongodb')->getMongoClient()->selectDB(Config::get('database.connections.mongodb.database'));

        return new MongoCollection($db, 'subjects');
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $doc
     */
    protected function buildOcrQueueData($doc)
    {
        $this->ocrData[$doc['_id']] = [
            'crop'   => Config::get('config.ocr_crop'),
            'ocr'    => '',
            'status' => 'pending',
            'url'    => $doc['accessURI']
        ];
    }

    /**
     * Chunk array for processing
     *
     * @return array
     */
    protected function getChunkQueueData()
    {
        return 0 === count($this->ocrData) ? [] : array_chunk($this->ocrData, Config::get('config.ocr_chunk'), true);
    }
}
