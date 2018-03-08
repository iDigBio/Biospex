<?php

namespace App\Services\Api;

use App\Services\Requests\HttpRequest;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\GenericProvider;

class NfnApi extends HttpRequest
{
    /**
     * @var GenericProvider
     */
    public $provider;

    /**
     * @var array
     */
    public $nfnSkipCsv = [];

    public function __construct()
    {
        $this->nfnSkipCsv = explode(',', config('config.nfnSkipCsv'));
    }

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
     * @throws GuzzleException
     */
    public function sendAuthorizedRequest($request)
    {
        $response = $this->provider->getHttpClient()->send($request);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get project.
     *
     * @param $projectId
     * @return string
     * @throws GuzzleException
     */
    public function getProjectUri($projectId)
    {
        return config('config.nfnApi.apiUri') . '/projects/' . $projectId;
    }

    /**
     * Get workflow.
     *
     * @param $workflowId
     * @return string
     */
    public function getWorkflowUri($workflowId)
    {
        return config('config.nfnApi.apiUri') . '/workflows/' . $workflowId;
    }

    /**
     * Get subject.
     *
     * @param $subjectId
     * @return string
     */
    public function getSubjectUri($subjectId)
    {
        return config('config.nfnApi.apiUri') . '/subjects/' . $subjectId;
    }

    /**
     * Get user.
     *
     * @param $userId
     * @return string
     */
    public function getUserUri($userId)
    {
        return config('config.nfnApi.apiUri') . '/users/' . $userId;
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
            || null === $expedition->nfnWorkflow->project
            || in_array($expedition->id, $this->nfnSkipCsv, false);
    }
}