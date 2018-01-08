<?php

namespace App\Jobs;

use App\Facades\DateHelper;
use App\Models\User;
use App\Notifications\GridCsvExport;
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GridExportCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var User
     */
    private $user;

    /**
     * @var
     */
    private $projectId;

    /**
     * @var null
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $projectId
     * @param null $expeditionId
     */
    public function __construct(User $user, $projectId, $expeditionId = null)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.beanstalkd.default'));
    }

    /**
     * Execute the job.
     *
     * @param MongoDbService $mongoDbService
     * @param Csv $csv
     * @return void
     * @throws \Exception
     */
    public function handle(
        MongoDbService $mongoDbService,
        Csv $csv
    )
    {
        try
        {
            $mongoDbService->setCollection('subjects');

            $query = null === $this->expeditionId ?
                ['project_id' => (int) $this->projectId] :
                ['project_id' => (int) $this->projectId, 'expedition_ids' => (int) $this->expeditionId];

            $cursor = $mongoDbService->find($query);
            $cursor->setTypeMap([
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array'
            ]);

            $docs = collect($cursor);
            $first = $docs->first();
            unset($first['_id'], $first['occurrence']);
            $header = array_keys($first);

            $file = config('config.export_reports_dir') . '/' . str_random() . '.csv';
            $csv->writerCreateFromPath($file);
            $csv->insertOne($header);

            $records = $docs->map(function($doc) use ($csv){
                unset($doc['_id'], $doc['occurrence']);
                $doc['orc'] = force_utf8($doc['ocr']);
                $doc['expedition_ids'] = trim(implode(', ', $doc['expedition_ids']), ',');
                $doc['updated_at'] = DateHelper::formatMongoDbDate($doc['updated_at'], 'Y-m-d H:i:s');
                $doc['created_at'] = DateHelper::formatMongoDbDate($doc['created_at'], 'Y-m-d H:i:s');

                return $doc;
            });

            $csv->insertAll($records->toArray());

            $this->user->notify(new GridCsvExport(trans('emails.grid_export_csv_complete'), $file));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new GridCsvExport(trans('emails.grid_export_csv_error', ['error' => $e->getMessage()])));
        }
    }
}
