<?php

namespace App\Console\Commands;

use League\OAuth2\Client\Provider\GenericProvider;
use App\Repositories\Contracts\Project;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Services\Api\NfnApi;

class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var Project
     */
    private $project;
    /**
     * @var NfnApi
     */
    private $api;

    /**
     * BuildAmChartData constructor.
     */
    public function __construct(NfnApi $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    public function fire()
    {
        $this->api->setProvider();
        $this->api->getClassifications(2318);

        /*
        $this->api->setClient(new Client(['cookies' => true, 'allow_redirects' => true]));
        $token = $this->api->setToken();
        dd($token);
        dd($this->api->getClassifications(2318));
        */
        /*
        - token:  include the token in an 'Authorization' header, as 'Bearer [insert token here]'
        - classifications api call:
        production - https://panoptes.zooniverse.org/api/classifications/project?workflow_id=2046
            (for workflow Herbarium_WeDigFLPlants' Mints of Florida)
        staging - https://panoptes-staging.zooniverse.org/api/classifications/project?project_id=1613
            (as an example based on project id)

        $stageUrl = 'https://panoptes-staging.zooniverse.org/oauth/token?';
        $prodUrl = 'https://panoptes.zooniverse.org/oauth/token?';
        $clientId = 'e8ee800bdd04438cdb6a4f9569e9720d539b16c87127885cbb960651a7b3b760';
        $clientSecret = '827117d68168579e1fecd37ceb858302c02c12313b6dff08d610f465d0db8707';
        $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob';



        $client = new \GuzzleHttp\Client(['cookies' => true, 'allow_redirects' => true]);

        $postResponse = $client->request('POST', $stageUrl, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            ]
        ]);

        //
        echo 'Sent post request' . PHP_EOL;

        $json = json_decode($postResponse->getBody());

        dd($json->access_token);
        */

    }

}

