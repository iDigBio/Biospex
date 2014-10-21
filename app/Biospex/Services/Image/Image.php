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

class Image
{
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
	 * Initialize the image service
	 */
	public function __construct ()
	{
		if (!$this->imagine)
		{
			$this->library = Config::get('config.library', 'gd');

			// Now create the instance
			if ($this->library == 'imagick') $this->imagine = new ImagickImagine;
			elseif ($this->library == 'gmagick') $this->imagine = new GmagickImagine;
			elseif ($this->library == 'gd') $this->imagine = new GdImagine;
			else                                 $this->imagine = new GdImagine;
		}
	}

	/**
	 * Resize image
	 * @param $sourceInfo
	 * @param $targetName
	 * @param $targetPath
	 * @param $width
	 * @param $height
	 */
	public function resizeImage ($sourceInfo, $targetName, $targetPath, $width, $height)
	{
		// Quality
		$quality = Config::get('config.quality', 100);

		// Directories and file names
		$fileName = $sourceInfo['basename'];
		$sourceFilePath = $sourceInfo['dirname'] . '/' . $fileName;
		$targetFilePath = $targetPath . '/' . $targetName;

		// Create directory if missing
		try
		{
			// Create dir if missing
			if (!File::isDirectory($targetPath) and $targetPath) @File::makeDirectory($targetPath);

			$this->imagine->open($sourceFilePath)
				->resize(new Box($width, $height))
				->save($targetFilePath, array('quality' => $quality));
		} catch (\Exception $e)
		{
			Log::error('[IMAGE SERVICE] Failed to resize image "' . $sourceFilePath . '" [' . $e->getMessage() . ']');
		}

		return;
	}
}