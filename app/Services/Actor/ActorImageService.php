<?php

namespace App\Services\Actor;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use App\Services\Image\Image;
use Illuminate\Filesystem\Filesystem;

class ActorImageService
{

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Image
     */
    public $image;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var
     */
    public $imageUris;

    /**
     * @var
     */
    public $existingImageUris;

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
     * @param Image $image
     * @param Filesystem $filesystem
     */
    public function __construct(Image $image, Filesystem $filesystem)
    {
        $this->client = new Client();
        $this->image = $image;
        $this->filesystem = $filesystem;
    }

    /**
     * @param $subjects
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * Add image to uri array.
     *
     * @param $index
     * @param $value
     */
    public function setImageUris($index, $value)
    {
        $this->imageUris[$index] = $value;
    }

    /**
     * Add image to existing uri array.
     *
     * @param $index
     * @param $value
     */
    public function setExistingImageUris($index, $value)
    {
        $this->existingImageUris[$index] = $value;
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
     * Build array of image uris.
     *
     * @param array $subjects
     * @param $dir
     * @throws \RuntimeException
     */
    public function buildImageUris($subjects, $dir)
    {
        foreach ($subjects as $subject)
        {
            if ($this->checkImageExists($subject->_id, $dir))
            {
                continue;
            }

            if ($this->checkUriExists($subject))
            {
                continue;
            }

            $this->setImageUris($subject->_id, str_replace(' ', '%20', $subject->accessURI));
        }
    }


    /**
     * Process expedition for export
     *
     * @param $dir
     */
    public function getImages($dir)
    {
        if (0 === count($this->subjects))
        {
            return;
        }

        $requests = function ($subjects) use ($dir)
        {
            foreach ($subjects as $index => $subject)
            {
                if ($this->checkImageExists($subject->_id, $dir))
                {
                    continue;
                }

                if ($this->checkUriExists($subject))
                {
                    continue;
                }

                yield $index => new Request('GET', str_replace(' ', '%20', $subject->accessURI));
            }
        };

        $pool = new Pool($this->client, $requests($this->subjects), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index) use ($dir)
            {
                $this->saveImage($response, $index, $dir);
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
     * @param $dir
     */
    public function saveImage($response, $index, $dir)
    {
        $image = $response->getBody();

        if ($image === '' || $response->getStatusCode() !== 200)
        {
            $this->setMissingImages($this->subjects[$index]);

            return;
        }

        try
        {
            $this->image->setImageInfoFromString($image);
        }
        catch (Exception $e)
        {
            $this->setMissingImages($this->subjects[$index]);

            return;
        }

        $ext = $this->image->getFileExtension();

        if ( ! $ext)
        {
            $this->setMissingImages($this->subjects[$index]);

            return;
        }

        $this->filesystem->put($dir . '/' . $this->subjects[$index]->_id . '.' . $ext, $image);
    }

    /**
     * Check if image exists.
     *
     * @param $dir
     * @param $id
     * @return bool
     */
    public function checkImageExists($id, $dir)
    {
        return count($this->filesystem->glob($dir . '/' . $id . '.*')) > 0;
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
     * @param array $files
     * @param array $attributes
     * @param $dir
     */
    public function processFiles(array $files, array $attributes, $dir)
    {
        if (count($files) === 0)
        {
            return;
        }

        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false)
            {
                continue;
            }

            $this->convertImage($attributes, $dir, $file);
        }
    }

    /**
     *
     * @param array $attributes
     * @param $dir
     * @param $file
     */
    private function convertImage(array $attributes, $dir, $file)
    {
        $fileName = $this->image->getFileName();

        foreach ($attributes as $attribute)
        {
            $this->writeImage($dir, $file, $attribute, $fileName);
        }

        $this->image->imagickClear();
    }

    /**
     * Write image to convert directory
     * @param $dir
     * @param $file
     * @param array $attribute
     * @param $fileName
     */
    private function writeImage($dir, $file, $attribute, $fileName)
    {
        $ext = array_key_exists('ext', $attribute) ? $attribute['ext'] : '.jpg';
        $imagePath = $dir . '/' . $fileName . $ext;
        if ( ! $this->filesystem->exists($imagePath))
        {
            $this->image->imagickFile($file);
            $this->image->imagickScale($imagePath, $attribute['width'], 0);

            return;
        }
    }
}