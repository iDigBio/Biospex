<?php

namespace App\Services\Image;

use Imagick;

class ImagickService extends ImageServiceBase
{
    /**
     * @var Imagick
     */
    public $imagick;

    /**
     * @param $source
     */
    public function createImagickObject($source = null)
    {
        $this->imagick = is_null($source) ? new Imagick() : new Imagick($source);
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 256);
        $this->imagick->setResourceLimit(Imagick::RESOURCETYPE_MAP, 256);
        //$this->imagick->setResourceLimit(\Imagick::RESOURCETYPE_AREA, 1512);
        //$this->imagick->setResourceLimit(\Imagick::RESOURCETYPE_FILE, 768);
        //$this->imagick->setResourceLimit(\Imagick::RESOURCETYPE_DISK, -1);
    }

    /**
     * @return bool
     */
    public function clearImagickObject()
    {
        return $this->imagick->clear();
    }

    /**
     * Read image from blob
     *
     * @param $source
     * @return bool
     */
    public function readImagickFromBlob($source)
    {
        return $this->imagick->readImageBlob($source);
    }

    /**
     * @param $source
     * @return bool
     */
    public function readImageFromPath($source)
    {
        return $this->imagick->readImage($source);
    }

    /**
     * @param $destination
     * @return bool
     */
    public function writeImagickImageToFile($destination)
    {
        $this->imagick->setImageFormat('jpg');
        $this->imagick->setOption('jpeg:extent', '600kb');
        $this->imagick->stripImage();

        return $this->imagick->writeImage($destination);
    }

    /**
     * Generate and save image.
     *
     * @param $name
     * @param $destination
     * @return bool
     */
    public function generateAndSaveImage($name, $destination)
    {

        if (isset($this->destinationImageWidth, $this->destinationImageHeight))
        {
            $this->imagick->scaleImage($this->destinationImageWidth, $this->destinationImageHeight, true);
        }

        return $this->writeImagickImageToFile($destination . '/' . $name . '.jpg');
    }
}