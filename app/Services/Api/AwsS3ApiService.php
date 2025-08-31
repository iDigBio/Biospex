<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
    private ?S3Client $client = null;

    /**
     * Get or create an S3 client with lazy loading and environment awareness
     * This prevents AWS S3 client instantiation during package discovery
     *
     * @throws \Exception
     */
    protected function getS3Client(): S3Client
    {
        if ($this->client === null) {
            $this->client = $this->createS3Client();
        }

        return $this->client;
    }

    /**
     * Create an S3 client with proper configuration validation and environment awareness
     */
    protected function createS3Client(): S3Client
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $key = config('filesystems.disks.s3.key');
        $secret = config('filesystems.disks.s3.secret');
        $region = config('filesystems.disks.s3.region');

        // Validate S3 configuration exists
        if (empty($bucket) || empty($key) || empty($secret) || empty($region)) {
            // For CI/testing environments, throw descriptive error or use mock
            if (app()->environment(['testing', 'local'])) {
                throw new \Exception('AWS S3 not available in testing environment. Required: AWS_BUCKET, AWS_ACCESS_KEY, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION');
            }

            throw new \Exception('AWS S3 credentials not configured. Required: AWS_BUCKET, AWS_ACCESS_KEY, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION');
        }

        return Storage::disk('s3')->getClient();
    }

    /**
     * Create a seekable stream to read a file from the bucket.
     *
     * @return false|resource
     *
     * @throws \Exception
     */
    public function createS3BucketStream(string $bucket, string $filePath, string $mode, bool $seekable = true)
    {
        $this->getS3Client()->registerStreamWrapper();

        $context = null;
        if ($seekable) {
            $context = stream_context_create([
                's3' => [
                    'seekable' => true,
                ],
            ]);
        }

        $s3Path = 's3://'.$bucket.'/'.$filePath;

        return fopen($s3Path, $mode, false, $context);
    }

    /**
     * Get file count in the bucket directory.
     *
     * Count returns the top directory so subtract 1.
     */
    public function getFileCount(string $bucket, string $dirPath): int
    {
        $objects = $this->getS3Client()->getIterator('ListObjects', [
            'Bucket' => $bucket,
            'Prefix' => $dirPath.'/',
        ]);

        return count(iterator_to_array($objects, false)) - 1;
    }
}
