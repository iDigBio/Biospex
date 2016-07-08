<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\WorkflowManager;
use Illuminate\Console\Command;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

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
     * BuildAmChartData constructor.
     */
    public function __construct(Project $project)
    {
        parent::__construct();
        
        $this->project = $project;
    }

    public function fire()
    {
        /*
         Panoptes::Client.new(
            url: test_url,
            auth_url: test_url,
            auth: { client_id: test_client_id, client_secret: test_client_secret }
          )
         */
        $clientId = 'e8ee800bdd04438cdb6a4f9569e9720d539b16c87127885cbb960651a7b3b760';
        $clientSecret = '827117d68168579e1fecd37ceb858302c02c12313b6dff08d610f465d0db8707';
        $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob';
        $testUrl = 'https://panoptes-staging.zooniverse.org';



        $client = new \GuzzleHttp\Client(['cookies' => true, 'allow_redirects' => true]);

        $postResponse = $client->request('POST', $signInUrl, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'form_params' => [
                'user[login]'    => 'biospex',
                'user[password]' => 'DD22edippizhpdxazRXO'
            ]
        ]);

        //
        echo 'Sent post request' . PHP_EOL;

        $tokenResponse = $client->request('GET', $tokenUrl, [
            'Accept'     => 'application/vnd.api+json; version=1',
            'Content-Type' => 'application/json'
        ]);

        echo 'Sent token request and retrieving response' . PHP_EOL;

        dd($tokenResponse->getBody()) . PHP_EOL;

    }

}
