<?php

namespace App\Jobs;

use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\Interfaces\OcrCsv;
use App\Repositories\Interfaces\OcrQueue;

class BuildOcrBatchesJob extends Job implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

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
        $this->onQueue(config('config.beanstalkd.ocr'));
    }

    /**
     * Handle Job.
     *
     * @param OcrQueue $ocrQueueRepo
     * @param OcrCsv $ocrCsvRepo
     * @param MongoDbService $mongoDbService
     */
    public function handle(
        OcrQueue $ocrQueueRepo,
        OcrCsv $ocrCsvRepo,
        MongoDbService $mongoDbService
    )
    {
        if (config('config.ocr_disable'))
        {
            return;
        }

        try
        {
            $this->buildOcrSubjectsArray($mongoDbService);

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
                    'data'       => ['subjects' => $chunk],
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
     * @param MongoDbService $mongoDbService
     */
    protected function buildOcrSubjectsArray($mongoDbService)
    {
        $mongoDbService->setCollection('subjects');
        $query = null === $this->expeditionId ?
            ['project_id' => $this->projectId, 'ocr' => ''] :
            ['project_id' => $this->projectId, 'expedition_ids' => $this->expeditionId, 'ocr' => ''];

        $results = $mongoDbService->find($query);

        foreach ($results as $doc)
        {
            $this->buildOcrQueueData($doc);
        }
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
