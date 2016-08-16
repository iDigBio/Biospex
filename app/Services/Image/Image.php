<?php namespace App\Services\Image;

use App\Services\Report\Report;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository as Config;

class Image
{
    /**
     * Instance of Gmagick
     */
    protected $imagick;

    /**
     * Geometry for imagemagick image.
     *
     * @var $geometry
     */
    protected $geometry = null;

    /**
     * Width of original image.
     *
     * @var
     */
    protected $width;

    /**
     * Height of original image.
     *
     * @var
     */
    protected $height;

    /**
     * Extension of image file.
     *
     * @var
     */
    protected $extension;

    /**
     * Path information about file.
     *
     * @var $pathinfo
     */
    protected $pathinfo;

    /**
     * Mime type of file.
     *
     * @var
     */
    protected $mimeType;

    /**
     * Array of image types to extensions from Config.
     *
     * @var
     */
    protected $imageTypeExtension = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var mixed
     */
    protected $maxSize;

    /**
     * @var mixed
     */
    protected $compression;

    /**
     * @var mixed
     */
    protected $equalizer;

    /**
     * Image constructor.
     *
     * @param FileSystem $filesystem
     * @param Config $config
     * @param Report $report
     */
    public function __construct(Filesystem $filesystem, Config $config, Report $report)
    {
        $this->imageTypeExtension = $config->get('config.images.imageTypeExtension');
        $this->maxSize = $config->get('config.images.maxSize');
        $this->compression = $config->get('config.images.compression');
        $this->equalizer = $config->get('config.images.equalizer');


        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->report = $report;
    }

    /**
     * Set pathinfo for image.
     *
     * @param $file
     */
    public function setImagePathInfo($file)
    {
        $this->pathinfo = pathinfo($file);
        $this->setExtension();
        $mime = array_search($this->pathinfo['extension'], $this->imageTypeExtension);
        $this->setMimeType($mime);
    }

    /**
     * Set image info from file.
     *
     * @param $file
     */
    public function setImageInfoFromFile($file)
    {
        $info = getimagesize($file);
        $this->width = $info[0];
        $this->height = $info[1];
        $this->setExtension($info['mime']);
        $this->setMimeType($info['mime']);
    }

    /**
     * Set image info from image string.
     *
     * @param $file
     */
    public function setImageInfoFromString($file)
    {
        $info = getimagesizefromstring($file);
        $this->width = $info[0];
        $this->height = $info[1];
        $this->setExtension($info['mime']);
        $this->setMimeType($info['mime']);
    }

    /**
     * Set mime type for image.
     *
     * @param null $mime
     */
    protected function setMimeType($mime)
    {
        $this->mimeType = $mime;
    }

    /**
     * Set extension from file.
     *
     * @param null $mime
     */
    public function setExtension($mime = null)
    {
        $this->extension = is_null($mime) ? $this->pathinfo['extension'] : $this->imageTypeExtension[$mime];
    }

    /**
     * Get image width.
     *
     * @return mixed
     */
    public function getImageWidth()
    {
        return ! is_null($this->geometry['width']) ? $this->geometry['width'] : $this->width;
    }

    /**
     * Get image height.
     *
     * @return mixed
     */
    public function getImageHeight()
    {
        return ! is_null($this->geometry['height']) ? $this->geometry['height'] : $this->height;
    }

    /**
     * Get directory name for image.
     *
     * @return mixed
     */
    public function getDirName()
    {
        return $this->pathinfo['dirname'];
    }

    /**
     * Get base name of the image file.
     *
     * @return mixed
     */
    public function getBaseName()
    {
        return $this->pathinfo['basename'];
    }

    /**
     * Get file name.
     *
     * @return mixed
     */
    public function getFileName()
    {
        return $this->pathinfo['filename'];
    }

    /**
     * Get file extension.
     *
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->extension;
    }

    /**
     * Return mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Read image using imagick.
     *
     * @param $file
     */
    public function imagickFile($file)
    {
        $f = fopen($file, 'r');
        fseek($f, 0);
        $this->imagick = new \Imagick();
        $this->imagick->setResourceLimit(6, 1);
        $this->imagick->readImageFile($f);
        $this->geometry = $this->imagick->getImageGeometry();
        fclose($f);
    }

    /**
     * Determine compression level to keep size of image under maximum.
     * @param $imageSize
     * @return float|int
     */
    public function setCompressionLevel($imageSize)
    {
        $compression = $this->compression;

        if ($imageSize > $this->maxSize)
        {
            $compression -= ($imageSize / $compression * $this->maxSize) / ($this->maxSize * $compression * $this->equalizer);
        }

        return floor($compression);
    }

    /**
     * Resize image.
     *
     * @param $target
     * @param int $width
     * @param int $height
     */
    public function imagickScale($target, $width = 0, $height = 0)
    {
        try
        {
            $this->imagick->scaleImage($width, $height);
            $this->imagick->stripImage();
            $this->imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $this->imagick->setImageCompressionQuality($this->setCompressionLevel(strlen($this->imagick->getImageBlob())));
            $this->imagick->writeImage($target);
        }
        catch (\Exception $e)
        {
            $this->report->addError('[IMAGE SERVICE] Failed to resize image. Target: "' . $target . ' [' . $e->getMessage() . ']');
            $this->report->reportSimpleError();
        }
    }

    /**
     * Destroy.
     */
    public function imagickDestroy()
    {
        $this->imagick->clear();
        $this->imagick->destroy();
        $this->geometry = null;
    }
}
