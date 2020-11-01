<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Notifications\GridCsvExportError;
use Exception;
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
     * @var int
     */
    private $projectId;

    /**
     * @var null
     */
    private $expeditionId;

    /**
     * @var mixed
     */
    private $filter;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param array $data
     */
    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->projectId = $data['projectId'];
        $this->expeditionId = $data['expeditionId'];
        $this->filter = $data['filter'];
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
    ) {
        try {
            $mongoDbService->setCollection('subjects');

            $query = null === $this->expeditionId ? ['project_id' => (int) $this->projectId] : [
                'project_id'     => (int) $this->projectId,
                'expedition_ids' => (int) $this->expeditionId,
            ];

            $cursor = $mongoDbService->find($query);
            $cursor->setTypeMap([
                'array'    => 'array',
                'document' => 'array',
                'root'     => 'array',
            ]);

            $docs = collect($cursor);
            $records = $docs->map(function ($doc) use ($csv) {
                $doc['expedition_ids'] = trim(implode(', ', $doc['expedition_ids']), ',');
                $doc['updated_at'] = DateHelper::formatMongoDbDate($doc['updated_at'], 'Y-m-d H:i:s');
                $doc['created_at'] = DateHelper::formatMongoDbDate($doc['created_at'], 'Y-m-d H:i:s');
                $doc['ocr'] = GeneralHelper::forceUtf8($doc['ocr']);
                $doc['occurrence'] = json_encode($doc['occurrence']);

                return $doc;
            });

            $csvName = Str::random().'.csv';
            $fileName = $csv->createReportCsv($records->toArray(), $csvName);

            $this->user->notify(new GridCsvExport($fileName));

        } catch (Exception $e) {
            $this->user->notify(new GridCsvExportError($e->getMessage()));
        }
    }
}
