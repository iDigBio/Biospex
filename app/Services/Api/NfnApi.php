<?php

namespace App\Services\Api;

use App\Exceptions\NfnApiException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Config\Repository as Config;
use Exception;

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
     * Send authorized request.
     *
     * @param $uri
     * @return mixed
     * @throws NfnApiException
     */
    private function authorizedRequest($uri)
    {
        try{
            $request = $this->provider->getAuthenticatedRequest(
                'GET',
                $uri,
                $this->cache->get('nfnToken')->getToken(),
                [
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/vnd.api+json; version=1'
                    ]
                ]
            );

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

        return $this->authorizedRequest($uri);
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

        return $this->authorizedRequest($uri);
    }

    /**
     * Returns classifications greater than last id.
     *
     * Values param should include project_id, workflow_id, last_id and page_size
     * @param array $values
     * @return mixed
     * @throws NfnApiException
     */
    public function getClassifications(array $values)
    {
        $this->checkAccessToken();

        $uri = $this->buildClassificationUri($values);

        $result = $this->authorizedRequest($uri);

        $this->results = array_merge($this->results, $result['classifications']);

        if ($result['meta']['classifications']['next_page'] !== null)
        {
            $values['page'] = $result['meta']['classifications']['next_page'];

            $this->getClassifications($values);
        }

        return $this->results;
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

}