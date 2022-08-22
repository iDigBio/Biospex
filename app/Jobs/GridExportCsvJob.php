<?php
/*
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
use App\Services\Api\AwsS3ApiService;
use App\Services\Grid\JqGridEncoder;
use App\Services\Csv\Csv;
use App\Facades\GeneralHelper;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class GridExportCsvJob
 *
 * @package App\Jobs
 */
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
        $this->postData = $data['postData'];
        $this->route = $data['route'];
        $this->onQueue(config('config.queues.default'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Grid\JqGridEncoder $jqGridEncoder
     * @param Csv $csv
     * @param \App\Services\Api\AwsS3ApiService $awsS3ApiService
     * @return void
     */
    public function handle(JqGridEncoder $jqGridEncoder, Csv $csv, AwsS3ApiService $awsS3ApiService)
    {
        $csvName = Str::random().'.csv';

        try {

            $cursor = $jqGridEncoder->encodeGridExportData($this->postData, $this->route, $this->projectId, $this->expeditionId);

            $header = $this->buildHeader();

            $bucket = config('filesystems.disks.s3.bucket');
            $filePath = config('config.reports_dir') . '/' . $csvName;

            $stream = $awsS3ApiService->createS3BucketStream($bucket, $filePath, 'w');
            $csv->writerCreateFromStream($stream);
            $csv->insertOne($header->keys()->toArray());

            $cursor->each(function ($subject) use ($header, $csv) {
                $subjectArray = $subject->getAttributes();
                $subjectArray['_id'] = (string) $subject->_id;
                $subjectArray['expedition_ids'] = trim(implode(', ', $subject->expedition_ids), ',');
                $subjectArray['updated_at'] = $subject->updated_at->toDateTimeString();
                $subjectArray['created_at'] = $subject->created_at->toDateTimeString();;
                $subjectArray['ocr'] = GeneralHelper::forceUtf8($subject->ocr);
                $subjectArray['occurrence'] = $this->decodeAndEncode($subject->occurrence->getAttributes());

                $merged = $header->merge($subjectArray);

                $csv->insertOne($merged->toArray());
            });

            if (!Storage::disk('s3')->exists(config('config.reports_dir').'/'.$csvName)) {
                throw new Exception(t('Csv export file is missing.'));
            }

            $route = route('admin.downloads.report', ['file' => base64_encode($csvName)]);
            $this->user->notify(new GridCsvExport($route, $this->projectId, $this->expeditionId));

        } catch (Exception $e) {
            $message = [
                'Project Id: ' . $this->projectId,
                'Expedition Id: ' . $this->expeditionId,
                'Error: ' . $e->getMessage(),
                'Trace: ' . $e->getTraceAsString()
            ];
            $this->user->notify(new GridCsvExportError($message));
            Storage::disk('s3')->delete(config('config.reports_dir').'/'.$csvName);
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
        unset($occurrence['_id'], $occurrence['updated_at'], $occurrence['created_at']);

        foreach ($occurrence as $field => $value) {
            if ($this->isJson($value)) {
                $value = json_decode($value);
            }

            $occurrence[$field] = $value;
        }

        return json_encode($occurrence);
    }

    /**
     * Check if value is json.
     *
     * @param $str
     * @return bool
     */
    public function isJson($str): bool
    {
        $json = json_decode($str);

        return $json !== false && ! is_null($json) && $str != $json;
    }
}
