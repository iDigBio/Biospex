<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Artisan;
use App\Interfaces\OcrCsv;
use App\Interfaces\OcrQueue;
use MongoCollection;

class BuildOcrBatchesJob extends Job implements ShouldQueue
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
     * BuildOcrBatchesJob constructor.
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
     * @throws \Exception
     */
    public function handle(
        OcrQueue $ocrQueueRepo,
        OcrCsv $ocrCsvRepo
    )
    {
        if (config('config.ocr_disable'))
        {
            return;
        }

        try
        {
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
                    'project_id' => $this->projectId,
                    'ocr_csv_id' => $ocrCsv->id,
                    'data'       => json_encode(['subjects' => $chunk]),
                    'total'      => $count,
                    'processed'  => 0,
                    'batch'      => $batch
                ]);
            }

            Artisan::call('ocr:poll');
        }
        catch (\Exception $e)
        {
            return;
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
     */
    protected function setCollection()
    {
        $databaseManager = app(DatabaseManager::class);
        $client = $databaseManager->connection('mongodb')->getMongoClient();
        $collection =$client->{config('database.connections.mongodb.database')}->subjects;

        return $collection;
    }

    /**
     * Build the ocr and send to the queue.
     *
     * @param $doc
     */
    protected function buildOcrQueueData($doc)
    {
        $this->ocrData[(string) $doc['_id']] = [
            'crop'   => config('config.ocr_crop'),
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
        return 0 === count($this->ocrData) ? [] : array_chunk($this->ocrData, config('config.ocr_chunk'), true);
    }
}
