<?php
/*
 * Copyright (c) 2022. Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Process;

use App\Models\ExportQueue;
use App\Models\Download;
use App\Services\Csv\AwsS3CsvService;

/**
 * Class CreateReportService
 */
readonly class CreateReportService
{
    /**
     * Construct.
     *
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Models\Download $download
     */
    public function __construct(private AwsS3CsvService $awsS3CsvService, private Download $download)
    {}

    /**
     * Create csv report.
     *
     * @param string $csvName
     * @param array $data
     * @return string|null
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createCsvReport(string $csvName, array $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        $header = array_keys($data[0]);

        $bucket = config('filesystems.disks.s3.bucket');
        $filePath = config('config.report_dir') . '/' . $csvName;

        $this->awsS3CsvService->createBucketStream($bucket, $filePath, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->csv->insertOne($header);
        $this->awsS3CsvService->csv->insertAll($data);

        return base64_encode($csvName);
    }

    /**
     * Save report.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param string $csvName
     */
    public function saveReport(ExportQueue $exportQueue, string $csvName)
    {
        $attributes = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id' => $exportQueue->actor_id,
            'type' => 'report'
        ];
        $values = [
            'expedition_id' => $exportQueue->expedition_id,
            'actor_id' => $exportQueue->actor_id,
            'file' => $csvName,
            'type' => 'report'
        ];

        $this->download->updateOrCreate($attributes, $values);
    }
}