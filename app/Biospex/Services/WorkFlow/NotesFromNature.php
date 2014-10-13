<?php namespace Biospex\Services\WorkFlow;
/**
 * NotesFromNature.php
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

use Cache;

class NotesFromNature extends WorkFlowAbstract
{
    /**
     * @var array
     */
    protected $states = array();

    protected $defaultState;

    /**
     * Current expedition being processed
     *
     * @var
     */
    protected $record;

    /**
     * Data Directory
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Full path to temp file director
     * @var
     */
    protected $tmpFileDir;

    /**
     * Meta xml from meta file
     * @var
     */
    protected $metaXml = null;

    /**
     * Image types from Config
     *
     * @var
     */
    protected $imgTypes;

    /**
     * CSV header array associated with meta file
     * @var array
     */
    protected $metaHeader = array();

    /**
     * Remote image column from csv import
     * @var
     */
    protected $bestQualityAccessUri = null;

    /**
     * Hold original image url
     * @var array
     */
    protected $originalImgUrl = array();

    /**
     * Stores missing urls
     * @var array
     */
    protected $missingImgUrl = array();

    /**
     * Missing image when retrieving via curl
     *
     * @var array
     */
    protected $missingImg = array();

    /**
     * Data array for images
     * @var array
     */
    protected $data = array();

    /**
     * Metadata array for images
     * @var array
     */
    protected $metadata = array();

    /**
     * Array to hold subjects and identifiers
     *
     * @var
     */
    protected $subjectArray;

    /**
     * Large image width for NfN
     *
     * @var int
     */
    private $largeWidth = 1540;

    /**
     * Small image width for NfN
     *
     * @var int
     */
    private $smallWidth  = 580;

	/**
	 * Debug argument from command line for testing
	 *
	 * @var
	 */
	protected $debug;

    /**
     * Set state variable
     *
     * @return array
     */
    protected function prepareStates()
    {
        $this->states = array(
            'export',
            'getStatus',
            'getResults',
            'completed',
            'analyze',
        );

        return;
    }

    /**
     * Set any configuration options
     */
    protected function setConfig()
    {
        $this->imgTypes = array(
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/tiff' => '.tiff',
        );

        return;
    }

	/**
	 * Set debug from command line
	 *
	 * @param bool $value
	 */
	public function setDebug($value = false)
	{
		$this->debug = $value;
		$this->report->setDebug($this->debug);
	}

    /**
     * Process current state
     *
     * @param $id
     */
    public function process($id)
    {
        $this->record = $this->expedition->findWith($id, ['project', 'subject.subjectDoc']);

        if (empty($this->record))
        {
            $this->report->addError(trans('errors.error_process', array('id' => $id)));
            $this->report->reportSimpleError();

            return;
        }

		try {
            $result = call_user_func(array($this, $this->states[$this->record->state]));

			if ( ! $result)
				return;
        }
        catch ( Exception $e )
        {
            $this->report->addError($e->getMessage());
            $this->report->reportSimpleError();
            $this->destroyDir($this->tmpFileDir);

            return;
        }

		$groupId = $this->record->Project->group_id;

        $this->record->state = $this->record->state+1;
        $this->expedition->save($this->record);

        $this->report->processComplete($groupId, $this->record->title);

        return;
    }

    /**
     * Get results
     */
    public function getResults()
    {
        return false;
    }

    /**
     * Get status
     */
    public function getStatus()
    {
        return false;
    }

    /**
     * Export the expedition
     *
     * @throws \RuntimeException
     */
    public function export()
    {
        $title = "{$this->record->id}-". (preg_replace('/[^a-zA-Z0-9]/', '', base64_encode($this->record->title)));
        $this->tmpFileDir = "{$this->dataDir}/$title";

        if ( ! $this->createDir($this->tmpFileDir))
            throw new \RuntimeException(trans('errors.error_create_dir', array('directory' => $this->tmpFileDir)));

        if ( ! $this->writeDir($this->tmpFileDir))
            throw new \RuntimeException(trans('errors.error_write_dir', array('directory' => $this->tmpFileDir)));

        if ( ! $this->buildImgDir())
            throw new \RuntimeException(trans('errors.error_build_image_dir', array('id' => $this->record->id)));

        $this->processImages();

        if ( ! $this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)))
            throw new \RuntimeException(trans('errors.error_save_file', array('directory' => "{$this->tmpFileDir}/details.js")));

        $this->executeCommand("tar -czf {$this->dataDir}/$title.tar.gz -C {$this->dataDir} $title");

        if ( ! empty($this->missingImgUrl) || ! empty($this->missingImg))
		{
			$groupId = $this->record->Project->group_id;
			$this->report->missingImages($groupId, $this->record->title, $this->missingImgUrl, $this->missingImg);
		}

        $this->createDownload($this->record->id, "$title.tar.gz");

        $this->destroyDir($this->tmpFileDir);

        return true;
    }

    /**
     * Process expedition for export
     */
    protected function buildImgDir()
    {
        $i = 0;
        foreach ($this->record->subject as $subject)
        {
            $this->subjectArray[$subject->id][] = $subject->object_id;

            if ( ! $this->setBestQualityUri())
                continue;

			$uri = $subject->subjectDoc->subject[$this->bestQualityAccessUri];
            if (empty($uri))
            {
                $this->missingImgUrl[] = array($subject->object_id);
                continue;
            }

			list($image, $ext) = $this->getImage($uri);

            if ( ! $image)
            {
                $this->missingImg[] = array($uri);
                continue;
            }

            $this->originalImgUrl[$subject->id.$ext] = $uri;
            $path = "{$this->tmpFileDir}/{$subject->id}{$ext}";

            if ( ! $this->saveFile($path, $image))
                continue;

            $i++;
        }

        return $i == 0 ? false : true;
    }

    /**
     * Retrieve short name being used for http://rs.tdwg.org/ac/terms/bestQualityAccessURI
     */
    protected function setBestQualityUri()
    {
		$result = $this->property->findByQualified('http://rs.tdwg.org/ac/terms/bestQualityAccessURI');
		if (empty($result))
			return false;

		$this->bestQualityAccessUri = $result->short;

        return true;
    }

    /**
     * Set xml from meta file
     * TODO: May not need this depending on whether bestQualityAccessURI will always be the same.
     * @param $metaId
     * @return bool
     */
    protected function setMetaData($metaId)
    {
		$key = md5($metaId);

		if (Cache::has($key))
		{
			$this->metaXml = Cache::get($key);
			return true;
		}

        if ( ! $meta = $this->meta->find($metaId))
        {
            $this->report->addError(trans('error_xml_meta', array('id' => $metaId)));
            $this->report->reportSimpleError();

            return false;
        }

		Cache::add($key, $meta->xml, 10);

        $this->metaXml = $meta->xml;

        return true;
    }

    /**
     * Retrieve image from url
     *
     * @param $url
     * @return array
     */
    protected function getImage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return array($image, $this->imgTypes[$contentType]);
    }

    /**
     * Process images for NfN for an expedition
     */
    protected function processImages()
    {
        $data = array();

        $files = $this->filesystem->files($this->tmpFileDir);

        $lrgPath = "{$this->tmpFileDir}/large";
        if ( ! $this->createDir($lrgPath))
            throw new \RuntimeException(trans('errors.error_create_dir', array('directory' => $lrgPath)));

        if ( ! $this->writeDir($lrgPath))
            throw new \RuntimeException(trans('errors.error_write_dir', array('directory' => $lrgPath)));

        $smPath = "{$this->tmpFileDir}/small";
        if ( ! $this->createDir($smPath))
            throw new \RuntimeException(trans('errors.error_create_dir', array('directory' => $smPath)));

        if ( ! $this->writeDir($smPath))
            throw new \RuntimeException(trans('errors.error_write_dir', array('directory' => $smPath)));

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
        $this->metadata['highResDir'] = $lrgPath;
        $this->metadata['lowResDir'] = $smPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
        foreach($files as $filePath) {

            list($width, $height, $type, $attr) = getimagesize($filePath); // $width, $height, $type, $attr
            $info = pathinfo($filePath); // $dirname, $basename, $extension, $filename

            $data['identifier'] = $this->subjectArray[$info['filename']];
            $data['original']['path'] = array($info['filename'], ".{$info['extension']}");
            $data['original']['name'] = $info['basename'];
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

            $lrgHeight = round(($height * $this->largeWidth) / $width);
            $lrgName = "{$info['filename']}.large.png";
            $lrgImgPath = "$lrgPath/$lrgName";
            $data['large']['name'] = "large/$lrgName";
            $data['large']['width'] = $this->largeWidth;
            $data['large']['height'] = $lrgHeight;

            $this->convertImage($filePath, $this->largeWidth, $lrgHeight, $lrgImgPath);


            $smallHeight = round(($height * $this->smallWidth) / $width);
            $smName = "{$info['filename']}.small.png";
            $smImgPath = "$smPath/$smName";
            $data['small']['name'] = "small/{$info['filename']}.small.png";
            $data['small']['width'] = $this->smallWidth;
            $data['small']['height'] = $smallHeight;

            $this->convertImage($filePath, $this->smallWidth, $smallHeight, $smImgPath);

            $this->metadata['images'][] = $data;

			$this->filesystem->delete($filePath);

            $i++;
        }
        $this->metadata['total'] = $i * 3;

        return true;
    }

    /**
     * Convert image and resize.
     *
     * @param $file
     * @param $width
     * @param $height
     * @param $newImgPath
     */
    protected function convertImage($file, $width, $height, $newImgPath)
    {
        $this->executeCommand("/usr/bin/convert $file -colorspace RGB -resize {$width}x{$height} $newImgPath");

        return;
    }
}