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
        $tokenUrl = 'https://panoptes-staging.zooniverse.org/oauth/authorize?response_type=token&client_id=24ad5676d5d25c6aa850dc5d5f63ec8c03dbc7ae113b6442b8571fce6c5b974c&redirect_uri=https://dev.biospex.org/nfn';
        $signInUrl = 'https://panoptes-staging.zooniverse.org/users/sign_in';

        $client = new \GuzzleHttp\Client(['cookies' => true, 'allow_redirects' => true]);

        $client->request('POST', $signInUrl, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'form_params' => [
                'user[login]'    => 'biospex',
                'user[password]' => 'DD22edippizhpdxazRXO'
            ]
        ]);
        
        //dd($postResponse->getHeader('Set-Cookie'));

        echo 'Sent post request' . PHP_EOL;
        
        /**
         * First parameter is for cookie "strictness"
         */
        //$cookie_jar = new \GuzzleHttp\Cookie\CookieJar;
        //$cookie_jar->extractCookies($postRequest, $postResponse);
        //dd($cookie_jar);
        
        //echo 'Created cookie jar' . PHP_EOL;
        
        $tokenResponse = $client->request('GET', $tokenUrl, [
            'Accept'     => 'application/vnd.api+json; version=1',
            'Content-Type' => 'application/json'
        ]);

        echo 'Sent token request and retrieving response' . PHP_EOL;
    }

}
