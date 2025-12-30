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
     * Construct
     */
    public function __construct()
    {
        // Only initialize S3 client if AWS configuration is available
        // This prevents null bucket errors during CI/CD builds
        if ($this->hasAwsConfiguration()) {
            $this->client = Storage::disk('s3')->getClient();
        }
    }

    /**
     * Check if AWS S3 configuration is available
     */
    private function hasAwsConfiguration(): bool
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');

        // We no longer check for key/secret because they are provided
        // by IAM Roles (server) or ~/.aws/credentials (local)
        return ! empty($bucket) && ! empty($region);
    }

    /**
     * Create a seekable stream to read file from bucket.
     *
     * @return false|resource
     *
     * @throws \Exception
     */
    public function createS3BucketStream(string $bucket, string $filePath, string $mode, bool $seekable = true)
    {
        if (! $this->client) {
            throw new \Exception('AWS S3 client not available. Please verify your AWS configuration (Region and Bucket).');
        }

        $this->client->registerStreamWrapper();

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
     * @throws \Exception
     */
    public function getFileCount(string $bucket, string $dirPath): int
    {
        if (! $this->client) {
            throw new \Exception('AWS S3 client not available. Please verify your AWS configuration (Region and Bucket).');
        }

        $prefix = rtrim($dirPath, '/').'/';

        $objects = $this->client->getIterator('ListObjects', [
            'Bucket' => $bucket,
            'Prefix' => $prefix,
        ]);

        $count = 0;
        foreach ($objects as $object) {
            // SKIP the "folder" placeholder (key ends with '/')
            if (! str_ends_with($object['Key'], '/')) {
                $count++;
            }
        }

        return $count;
    }
}
