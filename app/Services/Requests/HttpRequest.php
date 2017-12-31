<?php

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
     * Set access token.
     *
     * @param $token
     */
    protected function setAccessToken($token)
    {
        $accessToken = $this->provider->getAccessToken('client_credentials');
        Cache::put($token, $accessToken, 120);
    }

    /**
     * Check access token
     *
     * @param $token
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
     * @param $method
     * @param $uri
     * @param array $options
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function buildAuthenticatedRequest($method, $uri, array $options = [])
    {
        $request = $this->provider->getAuthenticatedRequest(
            $method,
            $uri,
            $this->accessToken->getToken(),
            $options
        );

        return $request;
    }

    /**
     * Build unauthenticated request
     *
     * @param $method
     * @param $uri
     * @param array $options
     * @return \Psr\Http\Message\RequestInterface
     */
    public function buildRequest($method, $uri, array $options = [])
    {
        return $this->provider->getRequest($method, $uri, $options);
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
        $responses = Pool::batch($this->getHttpClient(), $requests, [
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

        return $responses;
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

    public function retryDelay()
    {
        return function ($numberOfRetries)
        {
            return 1000 * $numberOfRetries;
        };
    }
}