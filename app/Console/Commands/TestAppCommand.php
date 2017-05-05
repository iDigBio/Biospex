<?php

namespace App\Console\Commands;


use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\SubjectContract;
use App\Services\Requests\HttpRequest;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;


class TestAppCommand extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(SubjectContract $subjectContract, HttpRequest $httpRequest, ProjectContract $projectContract)
    {
        $subjects = $subjectContract->setCacheLifetime(0)->findWhere(['expedition_ids', '=', 78]);

        $httpRequest->setHttpProvider();

        $requests = function ($subjects) use ($httpRequest)
        {
            foreach ($subjects as $index => $subject)
            {
                $filePath = storage_path('scratch') . '/' . $subject->_id . '.jpg';
                $uri = $subject->accessURI;

                yield $index => function ($poolOpts) use ($httpRequest, $uri, $filePath)
                {
                    $reqOpts = [
                        'sink' => $filePath
                    ];
                    if (is_array($poolOpts) && count($poolOpts) > 0)
                    {
                        $reqOpts = array_merge($poolOpts, $reqOpts); // req > pool
                    }

                    return $httpRequest->getHttpClient()->getAsync($uri, $reqOpts);
                };
            }
        };

        $pool = new Pool($httpRequest->getHttpClient(), $requests($subjects), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index)
            {
                echo 'Fulfilled ' . $index . PHP_EOL;
            },
            'rejected'    => function ($reason, $index)
            {
                dd($reason);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();


    }
}
