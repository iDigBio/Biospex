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

use Biospex\Services\Curl\Curl;
use Biospex\Services\Curl\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class Thumbnail extends Image{

	/**
	 * Output file path.
	 * @var
	 */
	protected $outputFile;

    /**
     * Output directory
     *
     * @var string
     */
    protected $outputDir;

	/**
	 * Default image.
	 *
	 * @var mixed
	 */
	private $defaultImg;

    /**
     * Thumbnail width.
     * @var
     */
    private $tnWidth;

    /**
     * Thumbnail height.
     * @var
     */
    private $tnHeight;

	/**
	 * Set variables.
	 */
	public function setVars()
	{
		// We can read the output path from our configuration file.
		$this->defaultImg = Config::get('config.images.thumbDefaultImg');
		$this->tnWidth = Config::get('config.images.thumbWidth');
		$this->tnHeight = Config::get('config.images.thumbHeight');
		$this->outputDir = Config::get('config.images.thumbOutputDir') . '/' . $this->tnWidth . '_' . $this->tnHeight;
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
        $this->createDir($this->outputDir);

		if (File::isFile($this->outputFileSm))
			return $this->outputFileSm;

		try {
            $rc = new Curl([$this, "saveThumbnail"]);
            $rc->options = [CURLOPT_HEADER => 0, CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1];
            $rc->window_size = 1;
            $request = new Request($url);
            $rc->add($request);
            $rc->execute();
		}
		catch (Exception $e)
		{
            \Log::critical($e->getMessage());
		}

		return $this->outputFileSm;
	}

    /**
     * Return thumbnail or create if not exists.
     *
     * @param $url
     * @return string
     */
    public function getThumbnail($url)
    {
        $this->setVars();

        if ( ! $file = $this->thumbFromUrl($url))
        {
            $file = $this->defaultImg;
        }

        return File::get($file);
    }

    /**
     * Save thumb file.
     *
     * @param $image
     * @param $info
     */
    public function saveThumbnail($image, $info)
    {
        $this->saveFile($this->outputFileLg, $image);
        $this->readImageMagickFile($this->outputFileLg);
        $this->resizeMagick($this->outputFileSm, $this->tnWidth, 0);
        $this->deleteImage($this->outputFileLg);
        $this->destroyImageMagick();

        return;
    }

	/**
	 * Set output file path.
	 *
	 * @param $url
	 * @return string
	 */
	public function setOutPutFile($url)
	{
		$filenameLg = md5($url) . '.jpg';
        $filenameSm = md5($url) . '.small.jpg';
		$this->outputFileLg = $this->outputDir . '/' . $filenameLg;
        $this->outputFileSm = $this->outputDir . '/' . $filenameSm;
	}

}