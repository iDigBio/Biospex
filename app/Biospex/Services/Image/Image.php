<?php namespace Biospex\Services\Image;

/**
 * GMagick.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Filesystem\Filesystem;
use Config, File;

class Image {

    /**
     * Instance of Gmagick
     */
    protected $image;

    /**
     * Geometry for imagemagick image.
     *
     * @var $geometry
     */
    protected $geometry;

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
     * Mime type of image.
     *
     * @var
     */
    protected $mime;

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
     * New image width.
     * @var
     */
    protected $newWidth;

    /**
     * New image height.
     * @var
     */
    protected $newHeight;

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
     * Initialize the image service.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->imageTypeExtension = Config::get('config.images.imageTypeExtension');

        return;
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
        $this->setMimeType();
    }

    /**
     * Set image size info.
     *
     * @param $file
     */
    public function setImageSizeInfo($file)
    {
        $size = is_string($file) ? getimagesizefromstring($file) : getimagesize($file);
        $this->width = $size[0];
        $this->height = $size[1];
        $this->setExtension($size['mime']);
        $this->setMimeType($size['mime']);
    }

    /**
     * Set mime type for image.
     *
     * @param null $mime
     */
    protected function setMimeType($mime = null)
    {
        $this->mime = is_null($mime) ? array_search($this->pathinfo['extension'], $this->imageTypeExtension) : $mime;
    }

    /**
     * Set extension from file.
     *
     * @param null $mime
     */
    public function setExtension($mime = null)
    {
        $this->extension = is_null($mime) ? $this->pathinfo['extension'] : $this->imageTypeExtension[$mime];

        return;
    }

    /**
     * Get image width.
     *
     * @return mixed
     */
    public function getImageWidth()
    {
        return ! empty($this->geometry['width']) ? $this->geometry['width'] : $this->width;
    }

    /**
     * Get image height.
     *
     * @return mixed
     */
    public function getImageHeight()
    {
        return ! empty($this->geometry['height']) ? $this->geometry['height'] : $this->height;
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
    public function readImageMagickFile($file)
    {
        $f = fopen($file, 'r');
        fseek($f, 0);
        $this->imagick = new \Imagick();
        $this->imagick->setResourceLimit(6,1);
        $this->imagick->readImageFile($f);
        fclose($f);

        return;
    }

    /**
     * Set geometry for imagemagick file.
     */
    public function setImageMagickGeometry()
    {
        $this->geometry = $this->imagick->getImageGeometry();

        return;
    }

    /**
     * Resize image.
     *
     * @param $target
     * @param int $width
     * @param int $height
     * @return bool
     */
    public function resizeMagick($target, $width = 0, $height = 0)
    {
        try
        {
            $scale = $this->imagick->scaleImage($width, $height);
            if ( ! $scale)
                return false;

            $write = $this->imagick->writeImage($target);
            if ( ! $write)
                return false;

            return true;
        }
        catch (\Exception $e)
        {
            \Log::error('[IMAGE SERVICE] Failed to resize image. Target: "' . $target . ' [' . $e->getMessage() . ']');
            return false;
        }
    }

    /**
     * Destroy.
     */
    public function destroyImageMagick()
    {
        $this->imagick->clear();
        $this->imagick->destroy();
    }

    /**
     * Save file.
     *
     * @param $path
     * @param $contents
     */
    protected function saveFile($path, $contents)
    {
        if ( ! $this->filesystem->put($path, $contents))
            throw new \RuntimeException(trans('emails.error_save_file'));

        return;
    }

    /**
     * Created directory.
     *
     * @param $dir
     */
    public function createDir($dir)
    {
        if ( ! $this->filesystem->isDirectory($dir))
        {
            if ( ! $this->filesystem->makeDirectory($dir, 0775, true))
                throw new \RuntimeException(trans('emails.error_create_dir', ['directory' => $dir]));
        }

        return;
    }

    public function deleteImage($file)
    {
        return $this->filesystem->delete($file);
    }

}

