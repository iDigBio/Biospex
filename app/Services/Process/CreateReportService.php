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
use App\Repositories\DownloadRepository;

/**
 * Class CreateReportService
 */
class CreateReportService
{
    /**
     * @var \App\Services\Process\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepository;

    /**
     * Construct.
     *
     * @param \App\Services\Process\AwsS3CsvService $awsS3CsvService
     * @param \App\Repositories\DownloadRepository $downloadRepository
     */
    public function __construct(
        AwsS3CsvService $awsS3CsvService,
        DownloadRepository $downloadRepository
    )
    {
        $this->awsS3CsvService = $awsS3CsvService;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * Create csv report.
     *
     * @param string $csvName
     * @param array $data
     * @return false|string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createCsvReport(string $csvName, array $data): bool|string
    {
        if (empty($data)) {
            return false;
        }

        $header = array_keys($data[0]);

        $bucket = config('filesystems.disks.s3.bucket');
        $filePath = config('config.reports_dir') . '/' . $csvName;

        $this->awsS3CsvService->createBucketStream($bucket, $filePath, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->insertOne($header);
        $this->awsS3CsvService->insertAll($data);

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

        $this->downloadRepository->updateOrCreate($attributes, $values);
    }
}