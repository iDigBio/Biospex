<?php

namespace App\Console\Commands;

use App\Exceptions\Handler;
use App\Jobs\NfnClassificationsCsvCreateJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Services\Api\NfnApi;
use App\Services\Report\Report;
use App\Services\Requests\HttpRequest;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Imagick;


class TestAppCommand extends Command
{

    use DispatchesJobs;
    public $job;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;
    /**
     * @var NfnApi
     */
    private $api;
    /**
     * @var Report
     */
    private $report;
    /**
     * @var Handler
     */
    private $handler;
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(
        NfnClassificationsCsvCreateJob $job,
        ExpeditionContract $expeditionContract,
        NfnApi $api,
        Report $report,
        Handler $handler,
        HttpRequest $request)
    {
        parent::__construct();

        $this->job = $job;
        $this->expeditionContract = $expeditionContract;
        $this->api = $api;
        $this->report = $report;
        $this->handler = $handler;
        $this->request = $request;
    }

    public function handle()
    {
        $uri = 'https://bisque.cyverse.org/image_service/image/00-NGwfGAjw4A7RUiRTCcY3bU?resize=4000&format=jpeg';

        $this->request->setHttpProvider();
        $request = $this->request->buildRequest('GET', $uri);
        $response = $this->request->getHttpClient()->send($request);
        $blob = $response->getBody()->getContents();
        //$blob = base64_encode($response->getBody()->getContents());

        //$this->job->handle($this->expeditionContract, $this->api, $this->report, $this->handler);
        $imgSource = storage_path('test.jpg');
        $imgDest = storage_path('testResize.jpg');

        $imagick = new \Imagick();
        $imagick->readImageBlob($blob);
        $imagick->setImageFormat('jpg');
        $imagick->setOption('jpeg:extent', '600kb');
        $imagick->writeImage($imgDest);


        //exec("convert $imgSource -define jpeg:extent=600kb $imgDest");
    }

}

/*
Building workflow uri: 2468
Building workflow uri: 2504
Building workflow uri: 2554
Building workflow uri: 2563
Building workflow uri: 2639
Building workflow uri: 2676
Building workflow uri: 2696
Building workflow uri: 2748
Building workflow uri: 2820
Building workflow uri: 2730
Building workflow uri: 2821
Building workflow uri: 2679
Building workflow uri: 2782
Building workflow uri: 2838
Building workflow uri: 2957
Building workflow uri: 3568
 */