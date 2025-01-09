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

use Aws\Credentials\CredentialProvider;
use Aws\Lambda\LambdaClient;

class AwsLambdaApiService
{
    protected LambdaClient $lambdaClient;

    /**
     * Construct
     */
    public function __construct()
    {
        $provider = CredentialProvider::defaultProvider();
        $this->lambdaClient = new LambdaClient([
            'credentials' => $provider,
            'version' => 'latest',
            'region' => config('config.aws.default_region'),
        ]);
    }

    /**
     * Invoke lambda client.
     */
    public function lambdaInvokeAsync(string $function, array $data): void
    {
        $this->lambdaClient->invoke([
            // The name your created Lamda function
            'FunctionName' => $function,
            'Payload' => json_encode($data),
            'InvocationType' => 'Event',
        ]);
    }

    /**
     * Invoke lambda client synchronously.
     */
    public function lambdaInvoke(string $function, array $data): \Aws\Result
    {
        return $this->lambdaClient->invoke([
            'FunctionName' => $function,
            'Payload' => json_encode($data),
        ]);
    }
}
