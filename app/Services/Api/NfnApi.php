<?php

namespace App\Services\Api;

use Illuminate\Config\Repository as Config;
use GuzzleHttp\Client;

class NfnApi
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var
     */
    private $access_token;

    /**
     * NfnApi constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = new Client(['cookies' => true, 'allow_redirects' => true]);
    }

    /**
     * Get access token.
     *
     * @return null
     */
    public function getToken()
    {
        return null === $this->access_token ? $this->token() : $this->access_token;
    }

    /**
     * Send request for token.
     *
     * @return null
     */
    private function token()
    {
        $response = $this->client->request('POST', $this->config->get('config.nfnApi.auth'), [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/vnd.api+json; version=1',
            'form_params'  => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->config->get('config.nfnApi.clientId'),
                'client_secret' => $this->config->get('config.nfnApi.clientSecret')
            ]
        ]);

        $json = json_decode($response->getBody());

        $this->access_token = isset($json->access_token) ? $json->access_token : null;

        echo $this->access_token . PHP_EOL;

        return $this->access_token;

    }

    /**
     * Send authorized request.
     *
     * @param $uri
     * @return mixed
     */
    private function authorizedRequest($uri)
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/vnd.api+json; version=1',
            'Authorization' => 'Bearer ' . $this->access_token
        ];

        $response = $this->client->request('GET', $uri, ['headers' => $headers]);

        return json_decode($response->getBody());
    }

    /**
     * /projects/id?display_name=display_name&approved=approved&beta=beta&live=live&include=include
     *
     * @param $id
     * @return mixed
     */
    public function getProject($id)
    {
        $uri = 'https://panoptes-staging.zooniverse.org/api/projects/1613';
        //$uri = $this->config->get('config.nfnApi.projects') . '/' . $id;

        return $this->authorizedRequest($uri);
    }


    public function getWorkflow($id)
    {
        $uri = $this->config->get('config.nfnApi.workflows') . '=' . $id;

        return $this->authorizedRequest($uri);
    }

    public function getClassification($id)
    {
        $uri = $this->config->get('config.nfnApi.classifications') . '/' . $id;

        return $this->authorizedRequest($uri);
    }
}