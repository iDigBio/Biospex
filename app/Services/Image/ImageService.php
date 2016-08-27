<?php

namespace App\Services\Image;

use App\Services\Report\Report;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;

/**
 * Class ImageService
 * @package App\Services\Image
 */
class ImageService
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var
     */
    protected $imgSource;

    /**
     * @var mixed
     */
    protected $imageTypeExtension;

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
    protected $sourceExtension; // jpg

    /**
     * @var
     * image/jpeg
     */
    protected $sourceMimeType;

    /**
     * @var
     * /path/to/image/directory
     */
    protected $sourceDirName;

    /**
     * @var
     * example.jpg
     */
    protected $sourceBaseName;

    /**
     * @var
     * example
     */
    protected $sourceFileName;

    /**
     * @var
     */
    protected $sourceAspectRatio;

    /**
     * @var mixed
     */
    protected $defaultImg;

    /**
     * @var mixed
     */
    protected $tnWidth;

    /**
     * @var mixed
     */
    protected $tnHeight;

    /**
     * @var string
     */
    protected $thumbDir;

    /**
     * Image constructor.
     *
     * @param FileSystem $filesystem
     * @param Config $config
     * @param Report $report
     */
    public function __construct(Filesystem $filesystem, Config $config, Report $report)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->report = $report;

        $this->imageTypeExtension = $config->get('config.images.imageTypeExtension');
        $this->defaultImg = $this->config->get('config.images.thumbDefaultImg');
        $this->tnWidth = $this->config->get('config.images.thumbWidth');
        $this->tnHeight = $this->config->get('config.images.thumbHeight');
        $this->thumbDir = $this->config->get('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;
    }

    /**
     * Set image source from file.
     *
     * @param $imgSource
     */
    public function setSourceFromFile($imgSource)
    {
        $this->setImageInfoFromFile($imgSource);
        $this->imgSource = imagecreatefromjpeg($imgSource);
    }

    /**
     * Set image source from string.
     *
     * @param $imgSource
     */
    public function setSourceFromString($imgSource)
    {
        $this->setImageInfoFromString($imgSource);
        $this->imgSource = imagecreatefromstring($imgSource);
    }

    /**
     * Destroy image source.
     */
    public function destroySource()
    {
        imagedestroy($this->imgSource);
        $this->imgSource = null;
    }

    /**
     * Set image info from file.
     *
     * @param $imgSource
     */
    protected function setImageInfoFromFile($imgSource)
    {
        $size = getimagesize($imgSource);
        $this->setImageInfo($size);
        $path = pathinfo($imgSource);
        $this->sourceExtension = $path['extension'];
        $this->sourceDirName = $path['dirname'];
        $this->sourceBaseName = $path['basename'];
        $this->sourceFileName = $path['filename'];
    }

    /**
     * Set image info from image string.
     *
     * @param $imgSource
     */
    protected function setImageInfoFromString($imgSource)
    {
        $size = getimagesizefromstring($imgSource);
        $this->setImageInfo($size);
        $this->sourceExtension = $this->imageTypeExtension[$this->sourceMimeType];
    }

    /**
     * Set common image information.
     *
     * @param $size
     */
    protected function setImageInfo($size)
    {
        list($this->sourceWidth, $this->sourceHeight) = $size;
        $this->sourceAspectRatio = $this->sourceWidth / $this->sourceHeight;
        $this->sourceMimeType = $size['mime'];
    }

    /**
     * Generate and save image.
     *
     * @param string $name
     * @param array $attributes [destination, extension, width, height]
     */
    public function generateAndSaveImage($name, array $attributes)
    {
        foreach ($attributes as $attribute)
        {
            list($destinationWidth, $destinationHeight) = $this->setDestinationWidthHeight($attribute['width'], $attribute['height']);

            $newImage = imagecreatetruecolor($destinationWidth, $destinationHeight);

            imagecopyresized($newImage, $this->imgSource, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $this->sourceWidth, $this->sourceHeight);
            imagejpeg($newImage, $attribute['destination'] . '/' . $name . $attribute['extension'], 80);
            imagedestroy($newImage);
        }
    }

    /**
     * Set width and height of destination image preserving aspect ratio.
     *
     * @param $width
     * @param $height
     * @return array
     */
    protected function setDestinationWidthHeight($width, $height)
    {
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
     * Return source extension.
     *
     * @return mixed
     */
    public function getSourceExtension()
    {
        return $this->sourceExtension;
    }

    /**
     * Return source mime type.
     *
     * @return mixed
     */
    public function getSourceMimeType()
    {
        return $this->sourceMimeType;
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

}