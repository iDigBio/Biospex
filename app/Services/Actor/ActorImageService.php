<?php

namespace App\Services\Actor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Services\Image\ImagickService;
use App\Services\Requests\HttpRequest;

class ActorImageService extends ActorServiceConfig
{

    /**
     * @var ImagickService
     */
    public $imagickService;

    /**
     * @var
     */
    public $gdService;

    /**
     * @var HttpRequest
     */
    public $httpRequest;

    /**
     * @var
     */
    public $nfnLrgWidth;

    /**
     * @var
     */
    private $missingImages = [];

    /**
     * ActorImageService constructor.
     *
     * @param ImagickService $imagickService
     * @param HttpRequest $httpRequest
     * @internal param ImageService $imageService
     */
    public function __construct(
        ImagickService $imagickService,
        HttpRequest $httpRequest
    )
    {
        $this->imagickService = $imagickService;
        $this->httpRequest = $httpRequest;
        $this->nfnLrgWidth = config('config.images.nfnLrgWidth');
    }

    /**
     * Process expedition for export.
     */
    public function getImages()
    {
        $this->httpRequest->setHttpProvider();

        $requests = function ()
        {
            foreach ($this->subjects as $index => $subject)
            {
                $file = $this->workingDirectory . '/' . $subject->_id . '.jpg';
                if ($this->checkPropertiesExist($subject) || $this->checkFileExists($file))
                {
                    $this->fireActorProcessedEvent();

                    continue;
                }

                $uri = str_replace(' ', '%20', $subject->accessURI);

                yield $index => new Request('GET', $uri);
            }
        };

        $pool = new Pool($this->httpRequest->getHttpClient(), $requests(), [
            'concurrency' => 5,
            'fulfilled'   => function ($response, $index)
            {
                $this->saveImage($response, $index);
            },
            'rejected'    => function ($reason, $index)
            {
                $this->fireActorProcessedEvent();
                $message = 'Could not retrieve image from uri.';
                $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, $message);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Save image to image path.
     *
     * @param $response
     * @param $index
     * @throws \Exception
     */
    private function saveImage($response, $index)
    {
        $image = $response->getBody()->getContents();
        $response->getBody()->close();

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            $message = 'Image string empty or status code not 200.';
            $this->setMissingImages($this->subjects[$index]->_id, $this->subjects[$index]->accessURI, $message);
            $this->fireActorProcessedEvent();

            return;
        }

        $this->imagickService->createImagickObject();
        $this->imagickService->readImagickFromBlob($image);
        $this->imagickService->setImageFormat();
        $this->imagickService->stripImage();
        $this->writeImagickFile($this->workingDirectory, $this->subjects[$index]->_id);

        $this->fireActorProcessedEvent();
    }

    /**
     * Add missing image information to array.
     *
     * @param $subjectId
     * @param $accessURI
     * @param $message
     */
    private function setMissingImages($subjectId, $accessURI, $message)
    {
        $this->missingImages[] = [
            'subjectId' => $subjectId,
            'accessURI' => $accessURI,
            'message' => $message
        ];
    }

    /**
     * Return missing images array.
     *
     * @return mixed
     */
    public function getMissingImages()
    {
        return $this->missingImages;
    }

    /**
     * Check properties to see if image needs to be retrieved.
     *
     * @param $subject
     * @return bool
     */
    private function checkPropertiesExist($subject)
    {
        if (empty($subject->accessURI))
        {
            $message = 'Image missing accessURI value';
            $this->setMissingImages($subject->_id, $subject->accessURI, $message);

            return true;
        }

        return false;
    }

    /**
     * Check files exists.
     *
     * @param $file
     * @return bool
     */
    public function checkFileExists($file)
    {
        return $this->isFile($file);
    }

    /**
     * Write imagick to file.
     *
     * @param string $dir
     * @param $fileName
     * @throws \Exception
     */
    public function writeImagickFile($dir, $fileName)
    {
        $destination = $dir . '/' . $fileName . '.jpg';
        if ( ! $this->imagickService->writeImagickImageToFile($destination))
        {
            $this->setMissingImages($fileName, '', 'Could not write image to ' . $dir);
        }

        $this->imagickService->clearImagickObject();
    }
}