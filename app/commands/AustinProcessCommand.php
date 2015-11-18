<?php

// Set so MAC csv line endings are detected correctly
ini_set("auto_detect_line_endings", "1");

use GuzzleHttp\Client;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use Illuminate\Console\Command;
use League\Csv\Reader;
use League\Csv\Writer;
use Rhumsaa\Uuid\Uuid;
use ForceUTF8\Encoding;

class AustinProcessCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'image:image';

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
     * Fire queue.
     */
    public function fire()
    {
        $iterator = $this->loadCsv();
        $requests = $this->buildRequest($iterator);
        $this->sendRequest($requests);
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
        $iterator = $reader->setOffset(1)->query(function($row) {
            return array_combine($this->header, $row);
        });

        return $iterator;
    }

    public function buildRequest($iterator)
    {
        $requests = [];
        foreach ($iterator as $key => $row) {
            $this->checkUtf8($row);
            $uri = $this->buildUri($row['catalogNumber']);
            $this->rows[$key] = $row;
            $this->rows[$key]['accessURI'] = $uri;
            $requests[] = $this->client->createRequest('GET', $uri, ['headers' => ['key' => $key]]);
        }

        return $requests;
    }

    public function sendRequest($requests)
    {
        Pool::send($this->client, $requests, [
            'pool_size' => 10,
            'complete'  => function (CompleteEvent $event) {
                $key = $event->getRequest()->getHeader('key');
                $image = $event->getResponse()->getBody()->getContents();
                $this->processImage($image, $key);
            },
            'error'     => function (ErrorEvent $event) {
                $key = $event->getRequest()->getHeader('key');
                $this->missingImages[] = $this->rows[$key];
            }
        ]);

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

        return;
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
     * @param $key
     * @internal param $info
     */
    public function processImage($image, $key)
    {
        if (empty($image)) {
            echo "Image for " . $this->rows[$key]['catalogNumber'] . " is not found." . PHP_EOL;
            $this->missingImages[] = $this->rows[$key];

            return;
        }

        $this->addToImageCsvArray($key);

        echo "Image for " . $this->rows[$key]['catalogNumber'] . " is found." . PHP_EOL;

        return;
    }

    public function addToImageCsvArray($key)
    {
        $this->imageCsv[] = [
            'coreid' => $this->rows[$key]['id'],
            'identifier' => Uuid::uuid4()->toString(),
            'accessURI' => $this->rows[$key]['accessURI']
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

        return;
    }
}
