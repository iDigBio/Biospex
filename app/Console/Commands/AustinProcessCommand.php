<?php namespace App\Console\Commands;

// Set so MAC csv line endings are detected correctly
ini_set("auto_detect_line_endings", "1");

use Illuminate\Console\Command;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use League\Csv\Reader;
use League\Csv\Writer;
use ForceUTF8\Encoding;
use Ramsey\Uuid\Uuid;

class AustinProcessCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'image:image';

    /**
     * The console command description.
     */
    protected $description = 'Used to handle Austin images';

    /**
     * @var
     */
    protected $header;

    /**
     * @var
     */
    private $rows;

    /**
     * @var
     */
    private $missingImages = [];

    /**
     * @var array
     */
    private $imageCsv = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * handle queue.
     */
    public function handle()
    {
        $rows = $this->loadCsv();
        $this->buildUris($rows);
        $this->sendRequest();
        $this->createCsv();

        echo "Process complete." . PHP_EOL;

        return;
    }

    public function loadCsv()
    {
        $reader = Reader::createFromPath(storage_path('austin/occurrences.csv'));
        $reader->setDelimiter(",");
        $reader->setEnclosure('"');

        $this->header = $reader->fetchOne();
        $rows = $this->reader->setOffset(1)->fetch(function($row) {
            return array_combine($this->header, $row);
        });

        return $rows;
    }

    public function buildUris($rows)
    {
        foreach ($rows as $key => $row) {
            $this->checkUtf8($row);
            $uri = $this->buildUri($row['catalogNumber']);
            $this->rows[$key] = $row;
            $this->rows[$key]['accessURI'] = $uri;
        }
    }

    public function sendRequest()
    {

        $requests = function ($uris) {
            foreach ($uris as $index => $url) {
                yield $index => new Request('GET', $url);
            }
        };

        $pool = new Pool($this->client, $requests($this->rows), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                $image = $response->getBody();
                $this->processImage($image, $index);
            },
            'rejected' => function ($reason, $index) {
                $this->missingImages[] = $this->rows[$index];
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();


        return;
    }

    public function checkUtf8(&$row)
    {
        array_walk(
            $row,
            function (&$value) {
                $value = Encoding::toUTF8($value);
            }
        );
    }

    public function buildUri($number)
    {
        $catalog = str_pad($number, 9, '0', STR_PAD_LEFT);
        $uri = "http://herbarium.bio.fsu.edu/showimage.php?Image=images/herbarium/jpegs/" . $catalog . ".jpg";

        return $uri;
    }

    /**
     * Callback function to save retrieved image from curl.
     *
     * @param $image
     * @param $index
     * @internal param $key
     * @internal param $info
     */
    public function processImage($image, $index)
    {
        if (empty($image)) {
            echo "Image for " . $this->rows[$index]['catalogNumber'] . " is not found." . PHP_EOL;
            $this->missingImages[] = $this->rows[$index];

            return;
        }

        $this->addToImageCsvArray($index);

        echo "Image for " . $this->rows[$index]['catalogNumber'] . " is found." . PHP_EOL;

    }

    public function addToImageCsvArray($index)
    {
        $this->imageCsv[] = [
            'coreid' => $this->rows[$index]['id'],
            'identifier' => Uuid::uuid4()->toString(),
            'accessURI' => $this->rows[$index]['accessURI']
        ];
    }

    /**
     * Get image key from headers.
     *
     * @param $headers
     * @return mixed
     */
    public function getImageKey($headers)
    {
        $header = $this->parseHeader($headers);

        return $header['key'];
    }

    /**
     * Parse header.
     *
     * @param $header
     * @return array
     */
    protected function parseHeader($header)
    {
        $headers = [];

        foreach (explode("\n", $header) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                $headers[$h[0]] = trim($h[1]);
            }
        }

        return $headers;
    }

    /**
     * Create attachment.
     *
     * @throws \Exception
     */
    public function createCsv()
    {
        $count = $this->missingImages;
        if ($count > 0) {
            $writer = Writer::createFromPath(new \SplFileObject(storage_path('austin') . "/missingImages.csv", 'a+'),
                'w');
            $writer->insertOne($this->header);
            $writer->insertAll($this->missingImages);
        }

        $count = $this->imageCsv;
        if ($count > 0) {
            $writer = Writer::createFromPath(new \SplFileObject(storage_path('austin') . "/images.csv", 'a+'), 'w');
            $writer->insertOne(['coreid', 'identifier', 'accessURI']);
            $writer->insertAll($this->imageCsv);
        }

    }
}
