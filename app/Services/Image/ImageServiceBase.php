<?php

namespace App\Services\Image;


class ImageServiceBase
{

    /**
     * @var
     */
    protected $sourceWidth;

    /**
     * @var
     */
    protected $sourceHeight;

    /**
     * @var
     */
    protected $sourceAspectRatio;

    /**
     * @var
     */
    protected $sourceDirName;

    /**
     * @var
     */
    protected $sourceBaseName;

    /**
     * @var
     */
    protected $sourceFileName;

    /**
     * @var
     */
    protected $sourceMimeType;

    /**
     * @var
     */
    protected $sourceExtension;

    /**
     * @var
     */
    protected $destinationImageWidth;

    /**
     * @var
     */
    protected $destinationImageHeight;

    /**
     * Set image info from file.
     *
     * @param $imgSource
     * @param bool $file
     * @return bool
     */
    public function setImageFromSource($imgSource, $file = true)
    {
        $info = $file ? getimagesize($imgSource) : getimagesizefromstring($imgSource);
        if ( ! $info)
        {
            return false;
        }

        $this->setImageInfo($info);

        ! $file ?: $this->setPathInfo($imgSource);

        return true;
    }

    /**
     * Set common image information.
     *
     * @param $info
     */
    protected function setImageInfo($info)
    {
        list($this->sourceWidth, $this->sourceHeight, $type, $attr) = $info;
        $this->sourceExtension = image_type_to_extension($type);
        $this->sourceMimeType = $info['mime'];
        $this->sourceAspectRatio = empty($this->sourceWidth) && empty($this->sourceHeight) ?
            : $this->sourceWidth / $this->sourceHeight;
    }

    /**
     * @param $imageSource
     */
    protected function setPathInfo($imageSource)
    {
        $path = pathinfo($imageSource);
        $this->sourceDirName = $path['dirname'];
        $this->sourceBaseName = $path['basename'];
        $this->sourceFileName = $path['filename'];
    }

    /**
     * Set width and height of destination image preserving aspect ratio.
     *
     * @return array
     */
    protected function getWidthHeight()
    {
        $width = $this->destinationImageWidth;
        $height = $this->destinationImageHeight;

        if (empty($this->sourceAspectRatio))
        {
            return [$width, $height];
        }

        if ($width / $height > $this->sourceAspectRatio)
        {
            $width = round($height * $this->sourceAspectRatio);
        }
        else
        {
            $height = round($width / $this->sourceAspectRatio);
        }

        return [$width, $height];
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
     * Return source width.
     *
     * @return mixed
     */
    public function getSourceWidth()
    {
        return $this->sourceWidth;
    }

    /**
     * Return source height.
     *
     * @return mixed
     */
    public function getSourceHeight()
    {
        return $this->sourceHeight;
    }

    /**
     * Return source directory name.
     *
     * @return mixed
     */
    public function getSourceDirName()
    {
        return $this->sourceDirName;
    }

    /**
     * Return source base name.
     *
     * @return mixed
     */
    public function getSourceBaseName()
    {
        return $this->sourceBaseName;
    }

    /**
     * Return source file name.
     *
     * @return mixed
     */
    public function getSourceFileName()
    {
        return $this->sourceFileName;
    }

    /**
     * Return source extension
     *
     * @return mixed
     */
    public function getSourceExtension()
    {
        return $this->sourceExtension;
    }

    /**
     * Return source mime type
     * @return mixed
     */
    public function getSourceMimeType()
    {
        return $this->sourceMimeType;
    }
}