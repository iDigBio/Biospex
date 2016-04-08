<?php

namespace App\Jobs;

use App\Events\PollOcrEvent;
use App\Models\Project;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Config;
use MongoCollection;

class BuildOcrBatches extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var
     */
    private $expeditionId;

    /**
     * @var
     */
    private $ocrQueue;

    /**
     * @var
     */
    private $ocrData;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param $expeditionId
     */
    public function __construct($project, $expeditionId = null)
    {

        $this->project = $project;
        $this->expeditionId = $expeditionId === null ? null : (int) $expeditionId;

        $this->ocrQueue = app(OcrQueue::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Config::get('config.ocr_disable'))
        {
            return;
        }

        if ( ! $this->checkOcrActorExists())
        {
            return;
        }

        if ( ! $this->checkOcrProcessing())
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
        $ocrCsv = app(OcrCsv::class)->create(['subjects' => '']);

        foreach ($data as $key => $chunk)
        {
            $batch = ($key === $lastKey) ? 1 : 0;
            $count = count($chunk);

            $this->ocrQueue->create([
                'project_id'        => $this->project->id,
                'ocr_csv_id'        => $ocrCsv->id,
                'data'              => json_encode(['subjects' => $chunk]),
                'subject_count'     => $count,
                'subject_remaining' => $count,
                'batch'             => $batch
            ]);
        }

        app(Dispatcher::class)->fire(new PollOcrEvent($this->ocrQueue));
    }

    /**
     * Check if project has ocr actor.
     * @return bool
     */
    protected function checkOcrActorExists()
    {
        foreach ($this->project->workflow->actors as $actor)
        {
            if (strtolower($actor->title) === 'ocr')
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether we should be processing this ocr request.
     * @return bool
     */
    protected function checkOcrProcessing()
    {
        $queue = $this->ocrQueue->findByProjectId($this->project->id);

        return null === $queue;
    }

    /**
     * Build the ocr subject array
     */
    protected function buildOcrSubjectsArray()
    {
        $cursor = $this->setCursor();

        foreach ($cursor as $id => $doc)
        {
            $doc['_id'] = $id;
            $subject = array_to_object($doc);
            $this->buildOcrQueueData($subject);
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
        $query = null === $this->expeditionId ?
            ['project_id' => $this->project->id, 'ocr' => ''] :
            ['project_id' => $this->project->id, 'expedition_ids' => $this->expeditionId, 'ocr' => ''];

        return $collection->find($query);
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $subject
     * @return array
     */
    protected function buildOcrQueueData($subject)
    {
        $this->ocrData[$subject->_id] = [
            'crop'   => Config::get('config.ocr_crop'),
            'ocr'    => '',
            'status' => 'pending',
            'url'    => $subject->accessURI
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
