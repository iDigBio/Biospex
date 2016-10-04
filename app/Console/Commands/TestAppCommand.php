<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use App\Exceptions\FileUnzipException;
use App\Services\Process\DarwinCore;
use App\Services\Process\MetaFile;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;


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

    private $client;

    /**
     * TestAppCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client();
    }

    public function fire()
    {
        $subjects = [
            'http://bovary.iplantcollaborative.org/image_service/image/00-LgixaYVirkZizsHUqqFkgi?resize=4000&format=jpeg',
            'http://herbarium.bio.fsu.edu/showimage.php?Image=images/herbarium/jpegs/000016394.jpg',
            'http://herbarium.bio.fsu.edu/showimage.php?Image=images/herbarium/jpegs/000016564.jpg'
        ];

        $result = $this->getImages($subjects);
    }

    /**
     * Process expedition for export.
     *
     * @param array $subjects
     */
    public function getImages($subjects)
    {
        $requests = function ($subjects)
        {
            foreach ($subjects as $index => $url)
            {
                yield $index => new Request('GET', str_replace(' ', '%20', $url));
            }
        };

        $pool = new Pool($this->client, $requests($subjects), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index)
            {
                $result = $this->saveImage($response, $index);
                echo 'result returned ' . $result . PHP_EOL;
                return $result;
            },
            'rejected'    => function ($reason, $index)
            {
                preg_match('/message\s(.*)\sresponse/', $reason, $matches);
                echo $matches[1] . PHP_EOL;
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * @param $response
     * @param $index
     * @return bool
     */
    private function saveImage($response, $index)
    {
        $image = $response->getBody()->getContents();

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            echo 'missing image index' . $index . PHP_EOL;
            return false;
        }

        echo 'saved image index ' . $index . PHP_EOL;

        return true;
    }

}
