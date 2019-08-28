<?php

namespace App\Console\Commands;

use App\Services\Requests\HttpRequest;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Requests\HttpRequest
     */
    private $request;

    /**
     * AppCommand constructor.
     */
    public function __construct(HttpRequest $request) {
        parent::__construct();
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $dir = 'charts';
        \Storage::makeDirectory($dir . '/15');
        $this->request->setHttpProvider();
        $response = $this->request->getHttpClient()->request('GET', 'http://localhost:3000/chart/15');
        \Storage::move($dir . '/15/amChart.png', $dir . '/15.png' );
        \Storage::deleteDirectory($dir . '/15');
        dd($response->getBody());
    }
}