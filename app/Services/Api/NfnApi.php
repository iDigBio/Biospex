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

    private function auth()
    {
        $response = $this->client->request('POST', $this->config->get('config.nfnApi.auth'), [
            'Content-Type' => 'application/json',
            'Accept' => 'application/vnd.api+json; version=1',
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id' => $this->config->get('config.nfnApi.clientId'),
                'client_secret' => $this->config->get('config.nfnApi.clientSecret')
            ]
        ]);

        $json = json_decode($response->getBody());

        $this->access_token = isset($json->access_token) ? $json->access_token : null;

        return $this->access_token;
                
    }

    public function getClassification($id)
    {
        $uri = 'https://panoptes-staging.zooniverse.org/api/projects/1613';
        $response = $this->client->request('GET', $uri, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/vnd.api+json; version=1',
            'x-csrf-token' => $this->access_token,
            'data' => $this->access_token
        ]);

        $json = json_decode($response->getBody());

        return $json;
    }

    public function token()
    {
        return null === $this->access_token ? $this->auth() : $this->access_token;
    }
}