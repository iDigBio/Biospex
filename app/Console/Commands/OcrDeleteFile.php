<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Config\Repository as Config;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class OcrDeleteFile extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrfile:delete {files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var Config
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = $this->argument('files');
        
        $requests = function ($files)
        {
            foreach ($files as $file)
            {
                $headers = ['API-KEY' => $this->config->get('config.ocr_api_key'), 'Content-Type' => 'application/x-www-form-urlencoded'];
                $body = http_build_query(['file' => $file]);
                yield $file => new Request('POST', $this->config->get('config.ocr_delete_url'), $headers, $body);
            }
        };
        
        $pool = new Pool(new Client(), $requests($files), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $file)
            {
                session_flash_push('success', "Deleted " . $file . " successfully.");
            },
            'rejected'    => function ($reason, $file)
            {
                session_flash_push('error', "Unable to delete file " . $file);
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}
