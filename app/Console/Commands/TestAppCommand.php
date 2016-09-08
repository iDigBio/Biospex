<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\OcrCsv;
use App\Repositories\Contracts\OcrQueue;
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

    protected $projectId;
    protected $expeditionId;
    private $ocrQueueRepo;
    private $ocrCsvRepo;

    private $ocrData;


    /**
     * TestAppCommand constructor.
     */
    public function __construct(OcrQueue $ocrQueueRepo, OcrCsv $ocrCsvRepo)
    {
        parent::__construct();

        $this->projectId = 25;
        $this->expeditionId = null;
        $this->ocrQueueRepo = $ocrQueueRepo;
        $this->ocrCsvRepo = $ocrCsvRepo;
    }

    public function fire()
    {
        if (Config::get('config.ocr_disable'))
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
        $ocrCsv = $this->ocrCsvRepo->create(['subjects' => '']);

        foreach ($data as $key => $chunk)
        {
            $batch = ($key === $lastKey) ? 1 : 0;
            $count = count($chunk);

            $this->ocrQueueRepo->create([
                'project_id'        => $this->projectId,
                'ocr_csv_id'        => $ocrCsv->id,
                'data'              => json_encode(['subjects' => $chunk]),
                'subject_count'     => $count,
                'subject_remaining' => $count,
                'batch'             => $batch
            ]);
        }
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
     * Query MongoDB and return cursor.
     *
     * @return MongoCollection
     * @throws \Exception
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
        $this->ocrData[(string) $doc['_id']] = [
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
