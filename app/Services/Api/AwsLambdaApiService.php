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
     * Create a Lambda client using config('services.aws') credentials
     */
    protected function createLambdaClient(): LambdaClient
    {
        $region = config('services.aws.region', 'us-east-2');

        // For testing: allow mock client
        if (app()->environment(['testing'])) {
            return new LambdaClient([
                'credentials' => false,
                'version' => 'latest',
                'region' => $region,
            ]);
        }

        $key = config('services.aws.credentials.key');
        $secret = config('services.aws.credentials.secret');

        if (empty($key) || empty($secret)) {
            throw new \Exception('AWS credentials missing. Required: AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY in .env');
        }

        return new LambdaClient([
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
            'version' => 'latest',
            'region' => $region,
        ]);
    }

    /**
     * Invoke Lambda asynchronously (fire-and-forget)
     */
    public function lambdaInvokeAsync(string $function, array $data): void
    {
        $invokeParams = [
            'FunctionName' => $function,
            'Payload' => json_encode($data),
            'InvocationType' => 'Event',
        ];

        $qualifier = config('services.aws.lambda_qualifier', '');
        if (! empty($qualifier)) {
            $invokeParams['Qualifier'] = $qualifier;
        }

        $this->getLambdaClient()->invoke($invokeParams);
    }

    /**
     * Invoke Lambda synchronously (wait for result)
     */
    public function lambdaInvoke(string $function, array $data): \Aws\Result
    {
        $invokeParams = [
            'FunctionName' => $function,
            'Payload' => json_encode($data),
        ];

        $qualifier = config('services.aws.lambda_qualifier', '');
        if (! empty($qualifier)) {
            $invokeParams['Qualifier'] = $qualifier;
        }

        return $this->getLambdaClient()->invoke($invokeParams);
    }
}
