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

/*
 * AWS Configuration Helper Functions
 *
 * Provides centralized AWS configuration access with proper validation
 * and environment-aware fallback strategies for CI/testing environments.
 */

if (! function_exists('aws_s3_bucket')) {
    /**
     * Get AWS S3 bucket name with validation and fallback support
     *
     * @return string The S3 bucket name
     *
     * @throws \Exception If bucket is not configured and not in testing environment
     */
    function aws_s3_bucket(): string
    {
        $bucket = config('filesystems.disks.s3.bucket');

        // Return bucket if properly configured
        if (! empty($bucket)) {
            return $bucket;
        }

        // For CI/testing environments, provide fallback or throw descriptive error
        if (app()->environment(['testing'])) {
            throw new \Exception('AWS S3 not available in testing environment. Required: AWS_BUCKET environment variable');
        }

        // For other environments, require proper configuration
        throw new \Exception('AWS S3 bucket not configured. Required: AWS_BUCKET environment variable');
    }
}

if (! function_exists('aws_s3_configured')) {
    /**
     * Check if AWS S3 is properly configured
     *
     * @return bool True if S3 is configured, false otherwise
     */
    function aws_s3_configured(): bool
    {
        $bucket = config('filesystems.disks.s3.bucket');
        $key = config('filesystems.disks.s3.key');
        $secret = config('filesystems.disks.s3.secret');
        $region = config('filesystems.disks.s3.region');

        return ! empty($bucket) && ! empty($key) && ! empty($secret) && ! empty($region);
    }
}
