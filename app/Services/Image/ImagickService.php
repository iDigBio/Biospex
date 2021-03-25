<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Image;

use Imagick;
use ImagickException;

/**
 * Class ImagickService
 *
 * @package App\Services\Image
 */
class ImagickService
{
    /**
     * @var Imagick
     */
    public $imagick;

    /**
     * @var
     */
    public $destinationImageWidth;

    /**
     * @var
     */
    public $destinationImageHeight;

    /**
     * Create Imagick object.
     */
    public function createImagickObject()
    {
        $this->imagick = new Imagick();
        $this->imagick->setResourceLimit(6, 1);
        //$this->imagick->setRegistry('temporary-path', '/efs');
    }

    /**
     * Clear Imagick object.
     *
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
     * @throws \ImagickException
     */
    public function readImagickFromBlob($source)
    {
        return $this->imagick->readImageBlob($source);
    }

    /**
     * @param $source
     * @return bool
     * @throws \ImagickException
     */
    public function readImageFromPath($source)
    {
        return $this->imagick->readImage($source);
    }

    /**
     * @param $width
     */
    public function setDestinationImageWidth($width)
    {
        $this->destinationImageWidth = $width;
    }

    /**
     * @param $height
     */
    public function setDestinationImageHeight($height)
    {
        $this->destinationImageHeight = $height;
    }

    /**
     * Resize image.
     */
    public function resizeImage()
    {
        if (isset($this->destinationImageWidth, $this->destinationImageHeight)) {
            $this->imagick->resizeImage($this->destinationImageWidth, $this->destinationImageHeight, Imagick::FILTER_LANCZOS, 1);
        }
    }

    /**
     * Set ImageMagick option.
     *
     * @param $option
     * @param $value
     * @throws \ImagickException
     */
    public function setOption($option, $value)
    {
        if ( ! $this->imagick->setOption($option, $value)){
            throw new ImagickException('Error setting Imagick option.');
        }
    }

    /**
     * Set imagick image format.
     *
     * @param string $format
     * @throws \ImagickException(
     */
    public function setImageFormat($format = 'jpg')
    {
        if ( ! $this->imagick->setImageFormat($format)) {
            throw new ImagickException('Error while setting image format.');
        }
    }

    /**
     * Strip metadata.
     *
     * @throws \Exception
     */
    public function stripImage()
    {
        if ( ! $this->imagick->stripImage()) {
            throw new ImagickException('Error while stripping image metadata.');
        }
    }

    /**
     * Write imagick image.
     *
     * @param $destination
     * @return bool
     */
    public function writeImagickImageToFile($destination)
    {
        return $this->imagick->writeImage($destination);
    }
}