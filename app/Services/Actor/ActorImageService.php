<?php

namespace App\Services\Actor;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use App\Services\Image\ImageService;
use Illuminate\Filesystem\Filesystem;

class ActorImageService
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var ImageService
     */
    public $imageService;

    /**
     * @var ActorFileService
     */
    public $actorFileService;

    /**
     * @var
     */
    public $missingImages = [];

    /**
     * @var
     */
    private $subjects;

    /**
     * ActorImageService constructor.
     *
     * @param ImageService $imageService
     * @param ActorFileService $actorFileService
     */
    public function __construct(ImageService $imageService, ActorFileService $actorFileService)
    {
        $this->client = new Client();
        $this->imageService = $imageService;
        $this->actorFileService = $actorFileService;
    }

    /**
     * Add missing image information to array.
     *
     * @param $subject
     * @param null $message
     */
    public function setMissingImages($subject, $message = null)
    {
        $accessURI = $message === null ? $subject->accessURI : $message;
        $this->missingImages[] = ['subjectId' => $subject->_id, 'accessURI' => $accessURI];
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
     * Process expedition for export.
     *
     * @param array $subjects
     * @param array $fileAttributes
     */
    public function getImages($subjects, $fileAttributes)
    {
        $this->subjects = $subjects;

        $attributes = array_key_exists(0, $fileAttributes) ? $fileAttributes : [$fileAttributes];

        $requests = function ($subjects) use ($attributes)
        {
            foreach ($subjects as $index => $subject)
            {
                if ($this->checkUriExists($subject))
                {
                    continue;
                }

                if ($this->checkImageExists($subject->_id, $attributes))
                {
                    continue;
                }

                yield $index => new Request('GET', str_replace(' ', '%20', $subject->accessURI));
            }
        };

        $pool = new Pool($this->client, $requests($subjects), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index) use ($attributes)
            {
                $this->saveImage($response, $index, $attributes);
            },
            'rejected'    => function ($reason, $index)
            {
                preg_match('/message\s(.*)\sresponse/', $reason, $matches);
                $this->setMissingImages($this->subjects[$index], $matches[1]);
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
     * @param $attributes
     */
    public function saveImage($response, $index, $attributes)
    {
        $image = $response->getBody();

        if ($this->checkStatus($image, $response, $index))
        {
            return;
        }

        try
        {
            $this->imageService->setSourceFromString($image);
            $this->imageService->generateAndSaveImage($this->subjects[$index]->_id, $attributes);
            $this->imageService->destroySource();
        }
        catch (Exception $e)
        {
            $this->removeErrorFiles($index, $attributes);
            $this->setMissingImages($this->subjects[$index], 'Could not save image to destination file');

            return;
        }
    }

    /**
     * Check status of image.
     *
     * @param $image
     * @param $response
     * @param $index
     * @return bool
     */
    protected function checkStatus($image, $response, $index)
    {
        if ($image === '' || $response->getStatusCode() !== 200)
        {
            $this->setMissingImages($this->subjects[$index], 'Image empty:' . $response->getStatusCode());

            return false;
        }
    }


    /**
     * Check if image exists.
     *
     * @param $id
     * @param array $attributes
     * @return bool
     */
    public function checkImageExists($id, $attributes)
    {
        $total = count($attributes);

        foreach ($attributes as $attribute)
        {
            $total -= count($this->actorFileService->filesystem->glob("{$attribute['destination']}/{$id}.{$attribute['extension']}"));
        }

        return $total === 0;
    }

    /**
     * Check if image exists
     *
     * @param $subject
     * @return bool
     */
    public function checkUriExists($subject)
    {
        if ($subject->accessURI === '')
        {
            $this->setMissingImages($subject);

            return true;
        }

        return false;
    }

    /**
     * Remove any subject images that existed when error occurred.
     *
     * @param $index
     * @param array $attributes
     */
    private function removeErrorFiles($index, $attributes)
    {
        foreach ($attributes as $attribute)
        {
            $path = $attribute['destination'] . '/' . $this->subjects[$index]->_id . $attribute['extension'];
            if ($this->actorFileService->filesystem->exists($path))
            {
                @unlink($path);
            }
        }
    }
}