<?php

namespace App\Services\Api;

use App\Exceptions\NfnApiException;
use App\Services\Requests\HttpRequest;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use League\OAuth2\Client\Provider\GenericProvider;
use Exception;

class NfnApi extends HttpRequest
{

    /**
     * @var CacheRepository
     */
    public $cache;

    /**
     * @var GenericProvider
     */
    public $provider;

    /**
     * Set provider for Notes From Nature
     * @param bool $auth
     */
    public function setProvider($auth = true)
    {
        $config = ! $auth ? [] :
            [
                'clientId'       => config('config.nfnApi.clientId'),
                'clientSecret'   => config('config.nfnApi.clientSecret'),
                'redirectUri'    => config('config.nfnApi.redirectUri'),
                'urlAccessToken' => config('config.nfnApi.tokenUri'),
            ];

        $this->setHttpProvider($config);
    }

    /**
     * Get generic provider
     * @return GenericProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Build authorized request.
     *
     * @param $uri
     * @param string $method
     * @param array $extra
     * @return \Psr\Http\Message\RequestInterface
     */
    public function buildAuthorizedRequest($method, $uri, array $extra = [])
    {
        $options = array_merge(
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/vnd.api+json; version=1'
                ]
            ],
            $extra
        );

        return $this->buildAuthenticatedRequest($method, $uri, $options);
    }

    /**
     * Send authorized request.
     *
     * @param $request
     * @return mixed
     * @throws NfnApiException
     */
    public function sendAuthorizedRequest($request)
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
        $uri = config('config.nfnApi.apiUri') . '/projects/' . $id;

        $request = $this->buildAuthorizedRequest('GET', $uri);

        return $this->sendAuthorizedRequest($request);
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
        $uri = config('config.nfnApi.apiUri') . '/workflows/' . $id;

        $request = $this->buildAuthorizedRequest('GET', $uri);

        return $this->sendAuthorizedRequest($request);
    }

    /**
     * Builds the uri specific for csv downloads by workflow.
     *
     * @param $workflowId
     * @return string
     */
    public function buildClassificationCsvUri($workflowId)
    {
        return config('config.nfnApi.apiUri') . '/workflows/' . $workflowId . '/classifications_export';
    }

    /**
     * Check needed variables.
     *
     * @param $expedition
     * @return bool
     */
    public function checkForRequiredVariables($expedition)
    {
        return null === $expedition
            || ! isset($expedition->nfnWorkflow)
            || null === $expedition->nfnWorkflow->workflow
            || null === $expedition->nfnWorkflow->project;
    }
}