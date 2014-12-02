<?php namespace Biospex\Services\Image;

/**
 * Thumbnail.php
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
use Imagine\Image\ImageInterface;
use Config, File;

class Thumbnail extends Image{

	/**
	 * Output file path.
	 * @var
	 */
	protected $outputFile;

	/**
	 * Default image.
	 *
	 * @var mixed
	 */
	protected $defaultImg;

	/**
	 * Output directory
	 *
	 * @var string
	 */
	protected $outputDir;

	/**
	 * Initialize the image service
	 */
	public function __construct ()
	{
		parent::__construct();

		// We can read the output path from our configuration file.
		$this->defaultImg = Config::get('config.images.thumbDefaultImg');
		$this->width = Config::get('config.images.thumbWidth');
		$this->height = Config::get('config.images.thumbHeight');
		$this->outputDir = Config::get('config.images.thumbOutputDir') . '/' . $this->width . '_' . $this->height;
	}

	/**
	 * Resize on the fly.
	 *
	 * @param $url
	 * @return string
	 */
	public function thumbFromUrl ($url)
	{
		$this->setOutPutFile($url);

		if (File::isFile($this->outputFile))
			return $this->outputFile;

		try {
			$image = $this->getImageFromUrl($url);
			$size = $this->createBox($this->width, $this->height);
			$mode = ImageInterface::THUMBNAIL_OUTBOUND;

			$this->createDirectory($this->outputDir);

			$this->imagine->load($image)->thumbnail($size, $mode)
				->save($this->outputFile, array('quality' => $this->quality));
		}
		catch (Exception $e)
		{
			return false;
		}

		return $this->outputFile;
	}

	/**
	 * Return thumbnail or create if not exists.
	 *
	 * @param $url
	 * @return string
	 */
	public function getThumbnail($url)
	{
		if ( ! $file = $this->thumbFromUrl($url))
		{
			$file = $this->defaultImg;
		}

		return File::get($file);
	}

	/**
	 * Set output file path.
	 *
	 * @param $url
	 * @return string
	 */
	public function setOutPutFile($url)
	{
		$filename = md5($url) . '.jpg';
		$this->outputFile = $this->outputDir . '/' . $filename;
	}
}