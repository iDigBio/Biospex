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

use App\Models\User;
use App\Models\Header;
use App\Notifications\GridCsvExport;
use App\Notifications\GridCsvExportError;
use App\Services\Grid\JqGridEncoder;
use App\Services\Csv\Csv;
use App\Facades\DateHelper;
use App\Facades\GeneralHelper;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * @var int
     */
    private $projectId;

    /**
     * @var int|null
     */
    private $expeditionId;

    /**
     * @var array
     */
    private $postData;

    /**
     * @var string
     */
    private $route;

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
        $this->postData = $data['filters'];
        $this->route = $data['route'];
        $this->onQueue(config('config.default_tube'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Grid\JqGridEncoder $jqGridEncoder
     * @param Csv $csv
     * @return void
     */
    public function handle(JqGridEncoder $jqGridEncoder, Csv $csv)
    {
        try {

            $query = $jqGridEncoder->encodeGridExportData($this->postData, $this->route, $this->projectId, $this->expeditionId);

            if (!$query->count()) {
                throw new Exception(t('No results were returned for the CSV export.'));
            }

            $header = $this->buildHeader();

            $csvName = Str::random().'.csv';
            $file = Storage::path(config('config.reports_dir').'/'.$csvName);
            $csv->writerCreateFromPath($file);
            $csv->insertOne($header->keys()->toArray());

            $query->chunk(1000, function($subjects) use($header, $csv) {
                $mapped = $subjects->map(function($subject) use($header) {
                    $subject->expedition_ids = trim(implode(', ',$subject->expedition_ids), ',');
                    $subject->updated_at = DateHelper::formatMongoDbDate($subject->updated_at, 'Y-m-d H:i:s');
                    $subject->created_at = DateHelper::formatMongoDbDate($subject->created_at, 'Y-m-d H:i:s');
                    $subject->ocr = GeneralHelper::forceUtf8($subject->ocr);
                    $subject->occurrence = $this->decodeAndEncode($subject->occurrence);

                    $subject = $header->merge($subject->toArray());

                    return $subject;
                });

                $csv->insertAll($mapped->toArray());
            });

            $this->user->notify(new GridCsvExport(base64_encode($csvName)));

        } catch (Exception $e) {
            $this->user->notify(new GridCsvExportError($e->getMessage()));
        }
    }

    /**
     * Build the header for export.
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildHeader()
    {
        $header = Header::where('project_id', $this->projectId)->first()->header['image'];
        array_unshift($header, '_id', 'project_id', 'id', 'expedition_ids', 'exported');
        array_push($header, 'ocr', 'occurrence', 'updated_at', 'created_at');
        return collect($header)->flip()->map(function($value, $key) {
            return "";
        });
    }

    /**
     * Decode fields from occurrence then encode to avoid errors.
     *
     * @param $occurrence
     * @return false|string
     */
    private function decodeAndEncode($occurrence)
    {
        foreach ($occurrence as $field => $value){
            $occurrence[$field] = is_array($value) ? $value : json_decode($value);
        }

        return json_encode($occurrence);
    }
}
