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

    public function auth()
    {
        $response = $this->client->request('POST', $this->config->get('config.nfnApi.auth'), [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id' => $this->config->get('config.nfnApi.clientId'),
                'client_secret' => $this->config->get('config.nfnApi.clientSecret')
            ]
        ]);

        $json = json_decode($response->getBody());

        $this->access_token = isset($json->access_toke) ? $json->access_token : null;
                
    }
}