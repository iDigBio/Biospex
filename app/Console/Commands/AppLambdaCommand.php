<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Repositories\ExportQueueFileRepository;
use App\Services\Api\AwsSignatureV4;
use App\Services\Requests\HttpRequest;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppLambdaCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'lambda:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test sqs lambda code';

    /**
     * @var \App\Services\Requests\HttpRequest
     */
    private HttpRequest $httpRequest;

    /**
     * @var \App\Services\Api\AwsSignatureV4
     */
    private AwsSignatureV4 $awsSignatureV4;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        HttpRequest $httpRequest,
        AwsSignatureV4 $awsSignatureV4,
        ExportQueueFileRepository $exportQueueFileRepository)
    {
        parent::__construct();
        $this->httpRequest = $httpRequest;
        $this->awsSignatureV4 = $awsSignatureV4;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
    }

    /**
     * Handle command.
     *
     * @return void
     */
    public function handle()
    {
        $this->httpApi();
    }

    /**
     * Test api method.
     *
     * @return void
     */
    public function httpApi()
    {
        $config = [
            'host'              => 'lambda.us-east-2.amazonaws.com',
            'uri'               => '/2015-03-31/functions/imageExportProcess/invocations',
            'queryString'       => '',
            'accessKey'         => config('config.aws_access_key'),
            'secretKey'         => config('config.aws_secret_key'),
            'region'            => config('config.aws_default_region'),
            'service'           => 'lambda',
            'httpRequestMethod' => 'POST',
            //'data'              => '',
            'debug'             => false,
        ];

        $this->httpRequest->setHttpProvider();

        $promiseGenerator = function ($data) use ($config) {
            foreach ($data as $values) {

                $config['data'] = json_encode($values);
                $this->awsSignatureV4->setConfig($config);
                $this->awsSignatureV4->createAwsSignature();
                $headers = $this->awsSignatureV4->getRequestHeaders();
                $requestUrl = $this->awsSignatureV4->getRequestUrl();

                yield function () use ($headers, $requestUrl, $config) {
                    return $this->httpRequest->getHttpClient()->requestAsync($config['httpRequestMethod'], $requestUrl, [
                        'headers' => $headers,
                        'body' => $config['data'],
                    ]);
                };
            }
        };

        // Create the generator that yields # of total promises.
        $data = $this->generateUrls(10);
        $promises = $promiseGenerator($data);

        // Set pool config.
        $poolConfig = [
            'concurrency' => 10,
            'fulfilled'   => function ($result) {
                echo $result->getBody() . PHP_EOL;
                //$this->echoResponse($result);
            },
            'rejected'    => function ($reason) {
                echo $reason->getBody() . PHP_EOL;
                //$this->echoResponse($reason);
            },
        ];

        $pool = $this->httpRequest->pool($promises, $poolConfig);
        $pool->promise()->wait();
    }

    /**
     * Temp method to output lambda response.
     *
     * @param $result
     * @return void
     */
    public function echoResponse($result)
    {
        $content = json_decode($result->getBody());
        $body = json_decode($content->body);
        echo $content->statusCode . PHP_EOL;
        echo $body->subjectId . PHP_EOL;
        echo $body->message . PHP_EOL;
    }

    /**
     * Temp method to generate urls for testing.
     *
     * @param int $total
     * @return array
     */
    public function generateUrls(int $total): array
    {
        $files = $this->exportQueueFileRepository->findBy('queue_id',1)->limit($total)->get();

        return $files->map(function($file) {
            return [
                'id'  => $file->subject_id,
                'url' => $file->url,
                'dir' => "scratch/testing-scratch"
            ];
        })->toArray();
    }
}