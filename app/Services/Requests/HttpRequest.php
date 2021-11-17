<?php
/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Requests;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Pool;

/**
 * Class HttpRequest
 *
 * @package App\Services\Requests
 */
class HttpRequest
{

    /**
     * @var GenericProvider
     */
    protected $provider;

    /**
     * @var
     */
    protected $accessToken;

    /**
     * Set authentication provider
     *
     * @param array $config
     * @return GenericProvider
     */
    public function setHttpProvider(array $config = [])
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        $reqOpts = null === $config ? ['handler' => $handlerStack] : array_merge([
            'handler'                 => $handlerStack,
            'urlAccessToken'          => '',
            'urlAuthorize'            => '',
            'urlResourceOwnerDetails' => ''
        ], $config);

        return $this->provider = new GenericProvider($reqOpts);
    }

    /**
     * Get http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        return $this->provider->getHttpClient();
    }

    /**
     * Set access token
     *
     * @param $token
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    protected function setAccessToken($token)
    {
        $accessToken = $this->provider->getAccessToken('client_credentials');
        Cache::put($token, $accessToken, 720);
    }

    /**
     * Check access token
     * @param $token
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function checkAccessToken($token)
    {
        if (null === Cache::get($token) || Cache::get($token)->hasExpired())
        {
            $this->setAccessToken($token);
        }

        $this->accessToken = Cache::get($token);
    }

    /**
     * Build authenticated request
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function buildAuthenticatedRequest(string $method, string $uri, array $options = [])
    {
        return $this->provider->getAuthenticatedRequest(
            $method,
            $uri,
            $this->accessToken->getToken(),
            $options
        );
    }

    /**
     * Pool batch requests.
     *
     * - concurrency: (int) Maximum number of requests to send concurrently
     * - options: Array of request options to apply to each request.
     * - fulfilled: (callable) Function to invoke when a request completes.
     * - rejected: (callable) Function to invoke when a request is rejected.
     *
     * @param $requests
     * @return array
     */
    public function poolBatchRequest($requests)
    {
        return Pool::batch($this->getHttpClient(), $requests, [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index)
            {
                return $index;
            },
            'rejected'    => function ($reason, $index)
            {
                return $index;
            }
        ]);
    }

    public function retryDecider()
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        )
        {
            // Limit the number of retries to 5
            if ($retries >= 5)
            {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException)
            {
                return true;
            }

            if ($response)
            {
                // Retry on server errors
                if ($response->getStatusCode() >= 500)
                {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @return \Closure
     */
    public function retryDelay()
    {
        return function ($numberOfRetries)
        {
            return 1000 * $numberOfRetries;
        };
    }
}