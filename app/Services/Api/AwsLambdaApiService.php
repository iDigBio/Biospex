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

use Aws\Lambda\LambdaClient;

class AwsLambdaApiService
{
    protected ?LambdaClient $lambdaClient = null;

    /**
     * Get or create Lambda client with lazy loading
     * This prevents AWS client instantiation during package discovery
     *
     * @throws \Exception
     */
    protected function getLambdaClient(): LambdaClient
    {
        if ($this->lambdaClient === null) {
            $this->lambdaClient = $this->createLambdaClient();
        }

        return $this->lambdaClient;
    }

    /**
     * Create a Lambda client with proper credential handling and environment awareness
     */
    protected function createLambdaClient(): LambdaClient
    {
        $accessKey = config('config.aws.access_key');
        $secretKey = config('config.aws.secret_key');
        $region = config('config.aws.default_region');

        // Validate credentials exist
        if (empty($accessKey) || empty($secretKey) || empty($region)) {
            // For CI/testing environments, use mock credentials to prevent errors
            if (app()->environment(['testing', 'local'])) {
                return new LambdaClient([
                    'credentials' => false, // Disable credentials for testing
                    'version' => 'latest',
                    'region' => $region ?: 'us-east-1',
                ]);
            }

            throw new \Exception('AWS credentials not configured. Required: AWS_ACCESS_KEY, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION');
        }

        return new LambdaClient([
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
            'version' => 'latest',
            'region' => $region,
        ]);
    }

    /**
     * Invoke lambda client asynchronously.
     *
     * @throws \Exception
     */
    public function lambdaInvokeAsync(string $function, array $data): void
    {
        $invokeParams = [
            'FunctionName' => $function,
            'Payload' => json_encode($data),
            'InvocationType' => 'Event',
        ];

        $qualifier = config('config.aws.lambda_export_qualifier');  // e.g., "" on dev, "production" on prod
        if (! empty($qualifier)) {  // Only add if non-empty (omits for $LATEST on dev)
            $invokeParams['Qualifier'] = $qualifier;
        }

        $this->getLambdaClient()->invoke($invokeParams);
    }

    /**
     * Invoke lambda client synchronously.
     *
     * @throws \Exception
     */
    public function lambdaInvoke(string $function, array $data): \Aws\Result
    {
        $invokeParams = [
            'FunctionName' => $function,
            'Payload' => json_encode($data),
        ];

        $qualifier = config('config.aws.lambda_export_qualifier');  // e.g., "" on dev, "production" on prod
        if (! empty($qualifier)) {  // FIX: Only add if non-empty (omits for $LATEST on dev)
            $invokeParams['Qualifier'] = $qualifier;
        }

        return $this->getLambdaClient()->invoke($invokeParams);
    }
}
