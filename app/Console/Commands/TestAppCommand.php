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
        $api = 'https://panoptes-staging.zooniverse.org/oauth/authorize?response_type=token&client_id=24ad5676d5d25c6aa850dc5d5f63ec8c03dbc7ae113b6442b8571fce6c5b974c&redirect_uri=https:biospex.org';

        $client = new Client();
        $res = $client->request('get', $api);
        echo $res->getStatusCode() . PHP_EOL;
        // "200"
        //echo $res->getHeader('content-type') . PHP_EOL;
        // 'application/json; charset=utf8'
        echo $res->getBody() . PHP_EOL;
        // {"type":"User"...'
    }

}
