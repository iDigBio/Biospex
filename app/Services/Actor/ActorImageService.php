<?php

namespace App\Services\Actor;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use App\Services\Image\ImagickService;
use App\Services\Image\GdService;
use App\Services\Requests\HttpRequest;

class ActorImageService extends ActorServiceBase
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
     * Set actor service configuration.
     *
     * @param ActorServiceConfig $config
     */
    public function setActorServiceConfig(ActorServiceConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Process expedition for export.
     */
    public function getImages()
    {
        $this->httpRequest->setHttpProvider();

        $requests = function ()
        {
            foreach ($this->config->subjects as $index => $subject)
            {
                if ($this->checkPropertiesExist($subject))
                {
                    $this->config->fireActorProcessedEvent();
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
                $this->config->fireActorProcessedEvent();
                $attributes = [
                    'subjectId' => $this->config->subjects[$index]->_id,
                    'accessURI' => $this->config->subjects[$index]->accessURI,
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
            'subjectId' => $this->config->subjects[$index]->_id,
            'accessURI' => $this->config->subjects[$index]->accessURI,
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
        if ( ! $this->gdService->writeImageToFile($this->config->workingDirectory . '/' . $this->config->subjects[$index]->_id . $ext, $image))
        {
            $this->setMissingImages($attributes);

            return;
        }

        $this->config->fireActorProcessedEvent();
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

        if (count(glob($this->config->workingDirectory . '/' . $subject->_id . '.*')) > 0)
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

        $destination = $this->config->tmpDirectory . '/' . $filename . '.jpg';
        if ( ! $this->imagickService->writeImagickImageToFile($destination))
        {
            \Log::alert("could not write file " . $file);
            $attributes = [
                'subjectId' => $filename,
                'accessURI' => '',
                'message' => 'Could not write image to file.'
            ];
            $this->setMissingImages($attributes);
            $this->clearAndFire();

            return false;
        }
        \Log::alert("file written " . $file);

        $this->clearAndFire();
        return true;
    }


    private function clearAndFire()
    {
        $this->imagickService->clearImagickObject();
        $this->config->fireActorProcessedEvent();
    }
}