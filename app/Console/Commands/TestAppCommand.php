<?php

namespace App\Console\Commands;

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
     * BuildAmChartData constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $projectUrl = 'https://panoptes-staging.zooniverse.org/api/workflows/2318';
        $tokenUrl = 'https://panoptes-staging.zooniverse.org/oauth/authorize?response_type=token&client_id=535759b966935c297be11913acee7a9ca17c025f9f15520e7504728e71110a27&redirect_uri=https://dev.biospex.org/nfn';
        $signInUrl = 'https://panoptes-staging.zooniverse.org/users/sign_in';

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
