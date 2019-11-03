<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use App\Facades\DateHelper;
use App\Facades\GeneralHelper;
use App\Models\User;
use App\Notifications\GridCsvExport;
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;

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
        $this->onQueue(config('config.default_tube'));
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
            $header = array_keys($first);

            $fileName = Str::random() . '.csv';
            $file = Storage::path(config('config.reports_dir') . '/' . $fileName);
            $csv->writerCreateFromPath($file);
            $csv->insertOne($header);

            $records = $docs->map(function($doc) use ($csv){
                $doc['expedition_ids'] = trim(implode(', ', $doc['expedition_ids']), ',');
                $doc['updated_at'] = $doc['updated_at']->toDateTime()->format('Y-m-d H:i:s');
                $doc['created_at'] = $doc['created_at']->toDateTime()->format('Y-m-d H:i:s');
                $doc['ocr'] = GeneralHelper::forceUtf8($doc['ocr']);
                $doc['occurrence'] = json_encode($doc['occurrence']);

                return $doc;
            });

            $csv->insertAll($records->toArray());

            $message = __('messages.grid_export_csv_complete', ['link' => route('admin.downloads.report', $fileName)]);

            $this->user->notify(new GridCsvExport($message));
        }
        catch (\Exception $e)
        {
            $this->user->notify(new GridCsvExport(__('messages.grid_export_csv_error', ['error' => $e->getMessage()])));
        }
    }
}
