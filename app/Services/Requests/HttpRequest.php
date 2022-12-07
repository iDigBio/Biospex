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

use DateTime;
use Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RetryMiddleware;
use Illuminate\Support\Facades\Cache;
use JetBrains\PhpStorm\Pure;
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Pool;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Http\Message\RequestInterface;

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
    protected GenericProvider $provider;

    /**
     * @var AccessTokenInterface
     */
    protected AccessTokenInterface $accessToken;

    /**
     * @var int
     */
    protected int $maxRetries = 3;

    /**
     * Set authentication provider
     *
     * @param array $config
     * @return GenericProvider
     */
    public function setHttpProvider(array $config = []): GenericProvider
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        $reqOpts = null === $config ? ['handler' => $handlerStack] : array_merge([
            'handler'                 => $handlerStack,
            'urlAccessToken'          => '',
            'urlAuthorize'            => '',
            'urlResourceOwnerDetails' => '',
        ], $config);

        return $this->provider = new GenericProvider($reqOpts);
    }

    /**
     * Get http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    #[Pure]
    public function getHttpClient(): ClientInterface
    {
        return $this->provider->getHttpClient();
    }

    /**
     * Set access token.
     *
     * @param $token
     * @return void
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    protected function setAccessToken($token)
    {
        $accessToken = $this->provider->getAccessToken('client_credentials');
        Cache::put($token, $accessToken, 720);
    }

    /**
     * Check access token
     *
     * @param $token
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function checkAccessToken($token)
    {
        if (null === Cache::get($token) || Cache::get($token)->hasExpired()) {
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
    protected function buildAuthenticatedRequest(string $method, string $uri, array $options = []): RequestInterface
    {
        return $this->provider->getAuthenticatedRequest($method, $uri, $this->accessToken->getToken(), $options);
    }

    /**
     * Pool batch requests.
     *
     * - concurrency: (int) Maximum number of requests to send concurrently
     * - options: Array of request options to apply to each request.
     * - fulfilled: (callable) Function to invoke when a request completes.
     * - rejected: (callable) Function to invoke when a request is rejected.
     *
     * @param \Iterator|array $requests $requests
     * @param int $concurrency
     * @return array
     */
    public function poolBatchRequest(\Iterator|array $requests, int $concurrency = 10): array
    {
        return Pool::batch($this->getHttpClient(), $requests, [
            'concurrency' => $concurrency,
            'fulfilled'   => function (Response $response, $index) {
                return [$index => $response];
            },
            'rejected'    => function (RequestException $reason, $index) {
                return [$index => $reason];
            },
        ]);
    }

    /**
     * Create pool and return.
     *
     * @param \Generator $promises
     * @param array $poolConfig
     * @return \GuzzleHttp\Pool
     */
    public function pool(Generator $promises, array $poolConfig): Pool
    {
        return new Pool($this->getHttpClient(), $promises, $poolConfig);
    }

    /**
     * Retry decider
     *
     * @return \Closure
     */
    public function retryDecider(): \Closure
    {
        return function (int $retries, Request $request, Response $response = null) {
            return $retries < $this->maxRetries && null !== $response && 429 === $response->getStatusCode();
        };
    }

    /**
     * Retry delay.
     *
     * @return \Closure
     */
    public function retryDelay(): \Closure
    {
        return function (int $retries, Response $response): int {
            if (! $response->hasHeader('Retry-After')) {
                return RetryMiddleware::exponentialDelay($retries);
            }

            $retryAfter = $response->getHeaderLine('Retry-After');

            if (! is_numeric($retryAfter)) {
                $retryAfter = (new DateTime($retryAfter))->getTimestamp() - time();
            }

            return (int) $retryAfter * 1000;
        };
    }
}