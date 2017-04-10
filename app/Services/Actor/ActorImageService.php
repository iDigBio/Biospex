<?php

namespace App\Services\Actor;

use App\Models\Actor;
use App\Services\File\FileService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use App\Services\Image\ImageService;
use App\Services\Requests\HttpRequest;

class ActorImageService
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var
     */
    private $missingImages = [];

    /**
     * @var
     */
    private $subjects;

    /**
     * @var ImageService
     */
    public $imageService;

    /**
     * @var FileService
     */
    public $fileService;

    /**
     * @var
     */
    protected $actor;

    /**
     * @var int
     */
    protected $processed = 0;

    /**
     * @var int
     */
    protected $subjectCount = 0;
    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * ActorImageService constructor.
     *
     * @param ImageService $imageService
     * @param FileService $fileService
     */
    public function __construct(ImageService $imageService, FileService $fileService, HttpRequest $httpRequest)
    {
        $this->client = new Client();
        $this->imageService = $imageService;
        $this->fileService = $fileService;
        $this->httpRequest = $httpRequest;
    }

    /**
     * Add missing image information to array.
     *
     * @param $subject
     * @param null $message
     */
    public function setMissingImages($subject, $message = null)
    {
        $this->missingImages[] = ['subjectId' => $subject->_id, 'accessURI' => $subject->accessURI, 'message' => $message];
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

    public function testImages($subjects, $destination, Actor $actor)
    {
        $this->subjects = $subjects;
        $this->subjectCount = count($this->subjects);
        $this->actor = $actor;

        $this->httpRequest->setHttpProvider();

        $requests = function ($subjects) use ($destination)
        {
            foreach ($subjects as $index => $subject)
            {
                $uri = str_replace(' ', '%20', $subject->accessURI);
                $filePath = $destination . '/' . $subject->_id . '.jpg';

                if ( ! $this->checkUriExists($subject))
                {
                    \Log::alert('checkUriExists failed');
                    $this->updateActor();

                    continue;
                }

                if ($this->checkImageExists($filePath))
                {
                    \Log::alert('checkImageExists');
                    $this->updateActor();

                    continue;
                }

                \Log::alert('Yeild Request: ' . $subject->accessURI);
                yield $index => function($poolOpts) use ($uri, $filePath) {
                    $reqOpts = [
                        'sink' => $filePath
                    ];
                    if (is_array($poolOpts) && count($poolOpts) > 0) {
                        $reqOpts = array_merge($poolOpts, $reqOpts); // req > pool
                    }

                    return $this->httpRequest->getHttpClient()->getAsync($uri, $reqOpts);
                };
            }
        };

        $responses = $this->httpRequest->poolBatchRequest($requests($subjects));
        $i = 0;
        foreach ($responses as $index => $response)
        {
            if ($response instanceof ServerException || $response instanceof ClientException)
            {
                \Log::alert('Error response: ' . $response->getMessage());
                continue;
            }
            \Log::alert('Success response' . $i++);
        }
    }


    /**
     * Process expedition for export.
     *
     * @param array $subjects
     * @param string $destination
     * @param Actor $actor
     */
    public function getImages($subjects, $destination, Actor $actor)
    {
        $this->subjects = $subjects;
        $this->subjectCount = count($this->subjects);
        $this->actor = $actor;

        $requests = function ($subjects) use ($destination)
        {
            \Log::alert('Building Requests');
            foreach ($subjects as $index => $subject)
            {
                if ( ! $this->checkUriExists($subject))
                {
                    \Log::alert('checkUriExists failed');
                    $this->updateActor();

                    continue;
                }

                if ($this->checkImageExists($subject->_id, $destination))
                {
                    \Log::alert('checkImageExists');
                    $this->updateActor();

                    continue;
                }

                \Log::alert('Yeild Request: ' . $subject->accessURI);
                yield $index => new Request('GET', str_replace(' ', '%20', $subject->accessURI));
            }
        };

        $pool = new Pool($this->client, $requests($subjects), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index) use ($destination, $actor)
            {
                \Log::alert('saveImage: ' . $index);
                $this->saveImage($response, $index, $destination);
            },
            'rejected'    => function ($reason, $index)
            {
                \Log::alert('rejected: ' . $index);
                $this->updateActor();
                $this->setMissingImages($this->subjects[$index], 'Could not retrieve image from uri.');
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
     * @param $destination
     */
    private function saveImage($response, $index, $destination)
    {
        \Log::alert('Entering saveImage');
        $image = $response->getBody()->getContents();

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            \Log::alert('Image empty: ' . $response->getStatusCode());
            $this->setMissingImages($this->subjects[$index], 'Image empty: ' . $response->getStatusCode());

            return;
        }

        $this->imageService->createImagickObject();
        \Log::alert('createdImageObject');

        if ( ! $this->imageService->readImagickFromBlob($image))
        {
            \Log::alert('Failed to read image blob: ' . $response->getStatusCode());
            $this->setMissingImages($this->subjects[$index], 'Could not create image from string: ' . $response->getStatusCode());

            return;
        }

        \Log::alert('generateAndSaveImage: ' . $this->subjects[$index]->_id);
        if ( ! $this->imageService->generateAndSaveImage($this->subjects[$index]->_id, $destination))
        {
            \Log::alert('Failed to generate Image: ' . $this->subjects[$index]->_id);
            $this->removeErrorFiles($index, $destination);
            $this->setMissingImages($this->subjects[$index], 'Could not save image to destination file');

            return;
        }

        \Log::alert('clearImageObject');
        $this->imageService->clearImagickObject();
        $this->updateActor();
    }

    /**
     * Check if image exists.
     *
     * @param string $filePath
     * @return bool
     */
    private function checkImageExists($filePath)
    {
        return file_exists($filePath);
    }

    /**
     * Check if image exists
     *
     * @param $subject
     * @return bool
     */
    private function checkUriExists($subject)
    {
        if ($subject->accessURI === '')
        {
            $this->setMissingImages($subject, 'Image missing accessURI');

            return false;
        }

        return true;
    }

    /**
     * Remove any subject images that existed when error occurred.
     *
     * @param $index
     * @param string $destination
     */
    private function removeErrorFiles($index, $destination)
    {
        $path = $destination . '/' . $this->subjects[$index]->_id . '.jpg';
        if ($this->fileService->filesystem->exists($path))
        {
            @unlink($path);
        }
    }

    /**
     * Update actor processed column.
     */
    private function updateActor()
    {
        $this->actor->pivot->processed++;
        $this->actor->pivot->save();
    }
}