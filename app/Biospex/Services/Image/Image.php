<?php namespace Biospex\Services\Image;

/**
 * Image.php
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
use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Gmagick\Imagine as GmagickImagine;
use Imagine\Gd\Imagine as GdImagine;
use Imagine\Image\Box;
use Config, File, Log;

class Image {

	/**
	 * Instance of the Imagine package
	 *
	 * @var \Imagine\Gd\Imagine
	 */
	protected $imagine;

	/**
	 * Type of library used by the service
	 *
	 * @var string
	 */
	protected $library;

	/**
	 * Mime type of file.
	 *
	 * @var
	 */
	protected $mimeType;

	/**
	 * @var mixed
	 */
	protected $width;

	/**
	 * @var mixed
	 */
	protected $height;

	/**
	 * Image quality.
	 *
	 * @var mixed
	 */
	protected $quality;

	/**
	 * Array of image types to extensions from Config.
	 *
	 * @var
	 */
	protected $imageTypeExtension = [];

	/**
	 * Initialize the image service
	 */
	public function __construct ()
	{
		if ( ! $this->imagine)
		{
			$this->library = Config::get('config.images.library', 'gd');

			// Now create the instance
			if ($this->library == 'imagick') $this->imagine = new ImagickImagine;
			elseif ($this->library == 'gmagick') $this->imagine = new GmagickImagine;
			elseif ($this->library == 'gd') $this->imagine = new GdImagine;
			else $this->imagine = new GdImagine;
		}

		$this->quality = Config::get('config.images.quality', 100);
		$this->imageTypeExtension = Config::get('config.images.imageTypeExtension');
	}

	/**
	 * Resize image.
	 *
	 * @param $sourceFilePath
	 * @param $targetFilePath
	 */
	public function resizeImage ($sourceFilePath, $targetFilePath)
	{
		try
		{
			$size = $this->createBox($this->width, $this->height);
			$this->imagine->open($sourceFilePath)->resize($size)
				->save($targetFilePath, array('quality' => $this->quality));
		} catch (\Exception $e)
		{
			Log::error('[IMAGE SERVICE] Failed to resize image. Source: "' . $sourceFilePath . ' :: Target: "' . $targetFilePath . ' [' . $e->getMessage() . ']');
		}

		return;
	}

	/**
	 * Create Imagine Box.
	 *
	 * @param $width
	 * @param $height
	 * @return Box
	 */
	protected function createBox($width, $height)
	{
		return new Box($width, $height);
	}

	/**
	 * Create directory.
	 *
	 * @param $path
	 */
	public function createDirectory($path)
	{
        try
        {
            if ( ! File::isDirectory($path))
            {
                File::makeDirectory($path, 775, true);
            }
        }
        catch (\Exception $e)
        {
            throw new \RuntimeException("Could not create directory." . $e->getMessage());
        }

        return;
	}

	/**
	 * Get file extension.
	 *
	 * @param $file
	 * @param bool $string
	 * @return string
	 */
	public function getExtension ($file, $string = false)
	{
		$info = ! $string ? getimagesize($file) : getimagesizefromstring($file);

		return isset($this->imageTypeExtension[$info['mime']]) ?
			$this->imageTypeExtension[$info['mime']] : false;
	}

	/**
	 * Return mime type.
	 *
	 * @return string
	 */
	public function getMimeType ()
	{
		return empty($this->mimeType) ? 'image/jpeg' : $this->mimeType;
	}

	/**
	 * Retrieve image from url
	 *
	 * @param $url
	 * @return array
	 */
	public function getImageFromUrl ($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, str_replace(" ", "%20", $url));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$image = curl_exec($ch);
		curl_close($ch);

		return $image;
	}

	/**
	 * Set width for resizing.
	 *
	 * @param $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * Set height for resizing.
	 *
	 * @param $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}
}