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
    public $workingDir;

    /**
     * @var
     */
    public $workingDirOrig;

    /**
     * @var
     */
    public $workingDirConvert;

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
     * Set working directories where images are processed.
     *
     * @param $path
     * @param $pathOrig
     * @param $pathConvert
     */
    public function setWorkingDirVars($path, $pathOrig, $pathConvert)
    {
        $this->workingDir = $path;
        $this->workingDirOrig = $pathOrig;
        $this->workingDirConvert = $pathConvert;
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
     * Add missing image information to array
     *
     * @param null $index
     * @param null $uri
     */
    public function setMissingImages($index = null, $uri = null)
    {
        if (($index !== null) && !($uri !== null)) {
            $this->missingImages[] = ['value' => $index . ' : ' . $uri];
        }

        if (($index === null) && ($uri !== null)) {
            $this->missingImages[] = ['value' => $uri];
        }

        if (($index !== null) && ($uri === null)) {
            $this->missingImages[] = ['value' => $index];
        }
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
     * @param $actor
     * @param array $subjects
     */
    public function buildImageUris($actor, $subjects)
    {
        foreach ($subjects as $subject)
        {
            if ($this->checkImageExists($subject->_id))
            {
                $this->setExistingImageUris($subject->_id, str_replace(' ', '%20', $subject->accessURI));
                continue;
            }

            if ($this->checkUriExists($subject))
            {
                continue;
            }

            $this->setImageUris($subject->_id, str_replace(' ', '%20', $subject->accessURI));
        }

        if (0 === count($this->imageUris) && 0 === count($this->existingImageUris))
        {
            throw new \RuntimeException(trans('emails.error_empty_image_uri', ['id' => $actor->pivot->id]));
        }

    }


    /**
     * Process expedition for export
     */
    public function getImages()
    {
        if (0 === count($this->imageUris)) {
            return;
        }

        $requests = function (array $uriArray) {
            foreach ($uriArray as $index => $url) {
                yield $index => new Request('GET', $url);
            }
        };

        $pool = new Pool($this->client, $requests($this->imageUris), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                $code = $response->getStatusCode();
                $image = $response->getBody();
                $this->saveImage($code, $index, $image);
            },
            'rejected' => function ($reason, $index) {
                $this->setMissingImages($index, $this->imageUris[$index]);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Save image to image path.
     *
     * @param $code
     * @param $index
     * @param $image
     */
    public function saveImage($code, $index, $image)
    {
        if ($image === '' || $code !== 200) {
            $this->setMissingImages($index, $this->imageUris[$index]);

            return;
        }

        try {
            $this->image->setImageInfoFromString($image);
        } catch (Exception $e) {
            $this->setMissingImages($index, $this->imageUris[$index]);

            return;
        }

        $ext = $this->image->getFileExtension();

        if ( ! $ext) {
            $this->setMissingImages($index, $this->imageUris[$index]);

            return;
        }

        $this->filesystem->put($this->workingDirOrig . '/' . $index . '.' . $ext, $image);
    }

    /**
     * Check if image exists
     *
     * @param $id
     * @return bool
     */
    public function checkImageExists($id)
    {
        return count($this->filesystem->glob($this->workingDirOrig . '/' . $id . '.*')) > 0;
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
            $this->setMissingImages($subject->id);

            return true;
        }

        return false;
    }

    /**
     * @param array $files
     * @param array $attributes
     */
    public function convert(array $files, array $attributes)
    {
        if (count($files) === 0)
        {
            return;
        }

        $imageUriArray = $this->mergeImageUri();

        $this->processImagickFile($files, $attributes, $imageUriArray);
    }

    /**
     * Process files.
     *
     * @param array $files
     * @param array $attributes
     * @param $imageUriArray
     */
    public function processImagickFile(array $files, array $attributes, $imageUriArray)
    {
        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);
            $fileName = $this->image->getFileName();

            if ($this->image->getMimeType() === false || ! $this->createImagickFile($file, $fileName, $imageUriArray))
            {
                continue;
            }

            $this->saveImagickImage($attributes, $fileName);

            $this->image->imagickDestroy();
        }
    }

    /**
     * create imagick file.
     *
     * @param $file
     * @param $fileName
     * @param $imageUriArray
     * @return bool
     */
    public function createImagickFile($file, $fileName, $imageUriArray)
    {
        try
        {
            $this->image->imagickFile($file);

            return true;
        }
        catch (Exception $e)
        {
            $this->setMissingImages($fileName, $imageUriArray[$fileName]);

            return false;
        }
    }

    /**
     * Save imagick file.
     *
     * @param array $attributes
     * @param $fileName
     */
    public function saveImagickImage(array $attributes, $fileName)
    {
        $ext = $this->image->getFileExtension();

        foreach ($attributes as $attribute)
        {
            $ext = isset($attribute['ext']) ?$attribute['ext'] : $ext;
            $imagePath = $this->workingDirConvert . '/' . $fileName . '.' . $ext;
            if ( ! $this->filesystem->exists($imagePath))
            {
                $this->image->imagickScale($imagePath, $attribute['width'], 0);
            }
        }
    }

    /**
     * Merge image and existing image arrays if job was stopped in the middle for some reason.
     *
     * @return array
     */
    public function mergeImageUri()
    {
        $imageUriCount = count($this->imageUris);
        $existingImageUriCount = count($this->existingImageUris);

        if ($imageUriCount !== 0 && $existingImageUriCount !== 0)
        {
            return array_merge($this->imageUris, $this->existingImageUris);
        }

        if ($imageUriCount === 0 && $existingImageUriCount !== 0)
        {
            return $this->existingImageUris;
        }

        if ($imageUriCount !== 0 && $existingImageUriCount === 0)
        {
            return $this->imageUris;
        }

    }
}