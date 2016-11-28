<?php

namespace App\Services\Api;

use App\Exceptions\NfnApiException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Config\Repository as Config;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

class NfnApi
{

    /**
     * @var CacheRepository
     */
    private $cache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GenericProvider
     */
    private $provider;

    /**
     * @var
     */
    private $results = [];

    /**
     * NfnApi constructor.
     * @param CacheRepository $cache
     * @param Config $config
     */
    public function __construct(CacheRepository $cache, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    public function setProvider()
    {
        $this->provider = new GenericProvider([
            'clientId'                => $this->config->get('config.nfnApi.clientId'),
            'clientSecret'            => $this->config->get('config.nfnApi.clientSecret'),
            'redirectUri'             => $this->config->get('config.nfnApi.redirectUri'),
            'urlAccessToken'          => $this->config->get('config.nfnApi.tokenUri'),
            'urlAuthorize'            => '',
            'urlResourceOwnerDetails' => ''
        ]);
    }

    /**
     * Build authorized request.
     *
     * @param $uri
     * @return \Psr\Http\Message\RequestInterface
     */
    private function buildRequest($uri)
    {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            $uri,
            $this->cache->get('nfnToken')->getToken(),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/vnd.api+json; version=1'
                ]
            ]
        );

        return $request;
    }

    /**
     * Send authorized request.
     *
     * @param $request
     * @return mixed
     * @throws NfnApiException
     */
    private function authorizedRequest($request)
    {
        try
        {
            $response = $this->provider->getHttpClient()->send($request);

            return json_decode($response->getBody()->getContents(), true);
        }
        catch (GuzzleException $e)
        {
            throw new NfnApiException($e);
        }
        catch (Exception $e)
        {
            throw new NfnApiException($e);
        }
    }

    /**
     * Get project.
     *
     * @param $id
     * @return mixed
     * @throws NfnApiException
     */
    public function getProject($id)
    {
        $this->checkAccessToken();

        $uri = $this->config->get('config.nfnApi.apiUri') . '/projects/' . $id;

        $request = $this->buildRequest($uri);

        return $this->authorizedRequest($request);
    }

    /**
     * Get workflow.
     *
     * @param $id
     * @return mixed
     * @throws NfnApiException
     */
    public function getWorkflow($id)
    {
        $this->checkAccessToken();

        $uri = $this->config->get('config.nfnApi.apiUri') . '/workflows/' . $id;

        $request = $this->buildRequest($uri);

        return $this->authorizedRequest($request);
    }

    /**
     * Set access token.
     */
    public function setAccessToken()
    {
        $accessToken = $this->provider->getAccessToken('client_credentials');
        $this->cache->put('nfnToken', $accessToken, 120);
    }

    /**
     * Check access token.
     */
    public function checkAccessToken()
    {
        if (null === $this->cache->get('nfnToken') || $this->cache->get('nfnToken')->hasExpired())
        {
            $this->setAccessToken();
        }
    }

    /**
     * Build uri for classifications.
     *
     * @param $values
     * @return string
     */
    private function buildClassificationUri($values)
    {
        return $this->config->get('config.nfnApi.apiUri') . '/classifications/project?' . http_build_query($values);
    }


    /**
     * Get Classifications.
     *
     * @param array $values
     * @return array
     * @throws NfnApiException
     */
    public function getClassifications(array $values)
    {
        $this->checkAccessToken();

        $uri = $this->buildClassificationUri($values);
        $request = $this->buildRequest($uri);
        $result = $this->authorizedRequest($request);

        $this->results = array_merge($this->results, $result['classifications']);

        $this->poolClassificationRequests($values, $result);

        return $this->results;
    }

    /**
     * @param array $values
     * @param $result
     * @throws NfnApiException
     */
    private function poolClassificationRequests(array $values, $result)
    {
        if ($result['meta']['classifications']['next_page'] === null)
        {
            return;
        }

        $pages = range(2, $result['meta']['classifications']['page_count']);

        $requests = function (array $pages) use ($values)
        {
            foreach ($pages as $page)
            {
                $values['page'] = $page;

                $uri = $this->buildClassificationUri($values);
                $request = $this->buildRequest($uri);

                yield $request;
            }
        };

        $pool = new Pool($this->provider->getHttpClient(), $requests($pages), [
            'concurrency' => 10,
            'fulfilled'   => function ($response)
            {
                $result = json_decode($response->getBody()->getContents(), true);
                $this->results = array_merge($this->results, $result['classifications']);
            },
            'rejected'    => function ($reason)
            {
                throw new NfnApiException($reason);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

}