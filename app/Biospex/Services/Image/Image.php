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

class Image {

    /**
     * Instance of Gmagick
     */
    protected $image;

    /**
     * @var $geometry
     */
    protected $geometry;

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
     * @param $file
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->imageTypeExtension = \Config::get('config.images.imageTypeExtension');

        return;
    }

    public function imageMagick($file)
    {
        $f = fopen($file, 'r');
        fseek($f, 0);
        $this->imagick = new \Imagick();
        $this->imagick->readimagefile($f);
        fclose($f);

        $this->geometry = $this->imagick->getImageGeometry();
        $this->pathinfo = pathinfo($file);
    }

    /**
     * Resize image.
     *
     * @param $target
     * @param int $width
     * @param int $height
     */
    public function resize($target, $width = 0, $height = 0)
    {
        try
        {
            $this->imagick->setcolorspace(\Imagick::COLORSPACE_RGB);
            $this->imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
            $this->imagick->writeImage($target);
        }
        catch (\Exception $e)
        {
            Log::error('[IMAGE SERVICE] Failed to resize image. Target: "' . $target . ' [' . $e->getMessage() . ']');
        }
    }

    /**
     * Get image width.
     *
     * @return mixed
     */
    public function getImageWidth()
    {
        return $this->geometry['width'];
    }

    /**
     * Get image height.
     *
     * @return mixed
     */
    public function getImageHeight()
    {
        return $this->geometry['height'];
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
     * Return extension from file.
     *
     * @return mixed
     */
    public function getExtension()
    {
        return $this->pathinfo['extension'];
    }

    /**
     * Get image height from file being checked. Used on existing file, not imagick file.
     *
     * @param $file
     * @param bool $var
     * @return array
     */
    public function getImageSizeFromFile($file, $var = false)
    {
        list($width, $height) = getimagesize($file);

        return !$var ? [$width, $height] : ($var == 'w' ? $width : $height);
    }

    /**
     * Get file extension from image string.
     *
     * @param $file
     * @return bool
     */
    public function getExtensionFromString($file)
    {
        $info = $this->getImageInfoFromString($file);

        return isset($this->imageTypeExtension[$info['mime']]) ? $this->imageTypeExtension[$info['mime']] : false;
    }

    /**
     * Get image info from string.
     *
     * @param $string
     * @return array
     */
    public function getImageInfoFromString($string)
    {
        $info = getimagesizefromstring($string);

        return $info;
    }

    /**
     * Return mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return empty($this->mimeType) ? 'image/jpeg' : $this->mimeType;
    }

    /**
     * Destroy.
     */
    public function destroy()
    {
        $this->imagick->clear();
        $this->imagick->destroy();
    }

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
