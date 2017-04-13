<?php

namespace App\Services\Image;

use App\Services\Report\Report;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;
use App\Exceptions\Handler;
use Imagick;

/**
 * Class ImageService
 * @package App\Services\Image
 */
class ImageService
{

    public $destinationImageWidth;
    public $destinationImageHeight;

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
     * @var Handler
     */
    public $handler;

    /**
     * @var Imagick
     */
    public $imagick;

    /**
     * ImageService constructor.
     *
     * @param Filesystem $filesystem
     * @param Config $config
     * @param Report $report
     * @param Handler $handler
     */
    public function __construct(Filesystem $filesystem, Config $config, Report $report, Handler $handler)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->report = $report;

        $this->imageTypeExtension = $config->get('config.images.imageTypeExtension');
        $this->defaultImg = $this->config->get('config.images.thumbDefaultImg');
        $this->tnWidth = $this->config->get('config.images.thumbWidth');
        $this->tnHeight = $this->config->get('config.images.thumbHeight');
        $this->thumbDir = $this->config->get('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;
        $this->handler = $handler;
    }

    /**
     * @param array $source
     */
    public function createImagickObject(array $source = [])
    {
        $this->imagick = empty($source) ? new Imagick() : new Imagick($source);
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
    public function writeImagickImage($destination)
    {
        $this->imagick->setImageFormat('jpg');
        $this->imagick->setOption('jpeg:extent', '600kb');
        $this->imagick->stripImage();
        return $this->imagick->writeImage($destination);
    }


    /**
     * Set image source from file.
     *
     * @param $imgSource
     */
    public function setSourceFromFile($imgSource)
    {
        $this->setImageInfoFromFile($imgSource);
        $this->createImagickObject($imgSource);
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

        return $this->writeImagickImage($destination . '/' . $name . '.jpg');
    }

    /**
     * Set width and height of destination image preserving aspect ratio.
     *
     * @return array
     */
    protected function setDestinationWidthHeight()
    {
        $width = $this->destinationImageWidth;
        $height = $this->destinationImageHeight;

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