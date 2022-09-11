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

use App\Services\Api\AwsS3ApiService;
use App\Services\Csv\Csv;

class AwsS3CsvService
{
    /**
     * @var \App\Services\Api\AwsS3ApiService
     */
    public AwsS3ApiService $awsS3ApiService;

    /**
     * @var \App\Services\Csv\Csv
     */
    public Csv $csv;

    /**
     * @var false|resource
     */
    private $stream;

    /**
     * @param \App\Services\Api\AwsS3ApiService $awsS3ApiService
     * @param \App\Services\Csv\Csv $csv
     * @return void
     */
    public function __construct(AwsS3ApiService $awsS3ApiService, Csv $csv)
    {
        $this->awsS3ApiService = $awsS3ApiService;
        $this->csv = $csv;
    }

    /**
     * Create bucket stream.
     *
     * @param string $bucket
     * @param string $path
     * @param string $mode
     * @return void
     */
    public function createBucketStream(string $bucket, string $path, string $mode)
    {
        $this->stream = $this->awsS3ApiService->createS3BucketStream($bucket, $path, $mode);
    }

    /**
     * Create csv write from s3 bucket stream.
     *
     * @return void
     */
    public function createCsvWriterFromStream()
    {
        $this->csv->writerCreateFromStream($this->stream);
    }

    /**
     * Create csv read from s3 bucket stream.
     *
     * @return void
     */
    public function createCsvReaderFromStream()
    {
        $this->csv->readerCreateFromStream($this->stream);
    }
}