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

namespace App\Services\Api;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;

class AwsS3ApiService
{
    /**
     * @var \Aws\S3\S3Client
     */
    private S3Client $client;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->client = Storage::disk('s3')->getClient();
    }

    /**
     * Create a seekable stream to read file from bucket.
     *
     * @param string $bucket
     * @param string $filePath
     * @param string $mode
     * @param bool $seekable
     * @return false|resource
     */
    public function createS3BucketStream(string $bucket, string $filePath, string $mode, bool $seekable = true)
    {
        $this->client->registerStreamWrapper();

        $context = null;
        if ($seekable) {
            $context = stream_context_create([
                's3' => [
                    'seekable' => true
                ]
            ]);
        }

        $s3Path = 's3://' . $bucket . '/' . $filePath;

        return fopen($s3Path, $mode, false, $context);
    }

    /**
     * Get file count in bucket directory.
     *
     * Count returns top directory so subtract 1.
     *
     * @param string $bucket
     * @param string $dirPath
     * @return int
     */
    public function getFileCount(string $bucket, string $dirPath): int
    {
        $objects = $this->client->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $dirPath . '/'
        ));

        return count(iterator_to_array($objects, false)) - 1;
    }
}