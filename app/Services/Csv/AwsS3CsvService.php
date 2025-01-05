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

namespace App\Services\Csv;

use App\Services\Api\AwsS3ApiService;

class AwsS3CsvService
{
    /**
     * @var false|resource
     */
    private $stream;

    /**
     * @return void
     */
    public function __construct(protected AwsS3ApiService $awsS3ApiService, public Csv $csv) {}

    /**
     * Create bucket stream.
     */
    public function createBucketStream(string $bucket, string $path, string $mode): void
    {
        $this->stream = $this->awsS3ApiService->createS3BucketStream($bucket, $path, $mode);
    }

    /**
     * Close bucket stream.
     */
    public function closeBucketStream(): bool
    {
        return fclose($this->stream);
    }

    /**
     * Create csv write from s3 bucket stream.
     */
    public function createCsvWriterFromStream(): void
    {
        $this->csv->writerCreateFromStream($this->stream);
    }

    /**
     * Create csv read from s3 bucket stream.
     */
    public function createCsvReaderFromStream(): void
    {
        $this->csv->readerCreateFromStream($this->stream);
    }

    /**
     * Get csv row count.
     */
    public function getCsvRowCount(string $dir): int
    {
        return $this->awsS3ApiService->getFileCount(config('filesystems.disks.s3.bucket'), $dir);
    }
}
