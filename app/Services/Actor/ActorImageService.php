<?php

namespace App\Services\Actor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Services\Image\ImagickService;
use App\Services\Image\GdService;
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
    private $missingImages = [];

    /**
     * ActorImageService constructor.
     *
     * @param ImagickService $imagickService
     * @param GdService $gdService
     * @param HttpRequest $httpRequest
     * @internal param ImageService $imageService
     */
    public function __construct(
        ImagickService $imagickService,
        GdService $gdService,
        HttpRequest $httpRequest
    )
    {
        $this->imagickService = $imagickService;
        $this->gdService = $gdService;
        $this->httpRequest = $httpRequest;
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
                if ($this->checkPropertiesExist($subject))
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
                $attributes = [
                    'subjectId' => $this->subjects[$index]->_id,
                    'accessURI' => $this->subjects[$index]->accessURI,
                    'message' => 'Could not retrieve image from uri.'
                ];
                $this->setMissingImages($attributes);
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
     */
    private function saveImage($response, $index)
    {
        $image = $response->getBody()->getContents();
        $response->getBody()->close();

        $attributes = [
            'subjectId' => $this->subjects[$index]->_id,
            'accessURI' => $this->subjects[$index]->accessURI,
            'message' => 'Image corrupt: ' . $response->getStatusCode()
        ];

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            $this->setMissingImages($attributes);

            return;
        }

        if ( ! $this->gdService->setImageFromSource($image, false))
        {
            $this->setMissingImages($attributes);

            return;
        }

        $ext = $this->gdService->getSourceExtension();
        if ( ! $this->gdService->writeImageToFile($this->workingDirectory . '/' . $this->subjects[$index]->_id . $ext, $image))
        {
            $this->setMissingImages($attributes);

            return;
        }

        $this->fireActorProcessedEvent();
    }

    /**
     * Add missing image information to array.
     *
     * @param $attributes
     */
    private function setMissingImages($attributes)
    {
        $this->missingImages[] = [
            'subjectId' => $attributes['subjectId'],
            'accessURI' => $attributes['accessURI'],
            'message' => ! isset($attributes['message']) ? null : $attributes['message']
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
            $attributes = [
                'subjectId' => $subject->_id,
                'accessURI' => $subject->accessURI,
                'message' => 'Image missing accessURI value'
            ];
            $this->setMissingImages($attributes);

            return true;
        }

        if (count(glob($this->workingDirectory . '/' . $subject->_id . '.*')) > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Write imagick to file.
     *
     * @param string $file
     * @param $filename
     * @return bool
     * @throws \Exception
     */
    public function writeImagickFile($file, $filename)
    {
        $this->imagickService->createImagickObject($file);

        $destination = $this->tmpDirectory . '/' . $filename . '.jpg';
        if ( ! $this->imagickService->writeImagickImageToFile($destination))
        {
            $attributes = [
                'subjectId' => $filename,
                'accessURI' => '',
                'message' => 'Could not write image to file.'
            ];
            $this->setMissingImages($attributes);
            $this->clearAndFire();

            return false;
        }

        $this->clearAndFire();
        return true;
    }


    private function clearAndFire()
    {
        $this->imagickService->clearImagickObject();
        $this->fireActorProcessedEvent();
    }
}