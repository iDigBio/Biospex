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

class NotesFromNature extends WorkFlowAbstract
{
    /**
     * @var array
     */
    protected $states = array();

	/**
	 * Id for the workflow
	 * @var null
	 */
	protected $workflowId = null;

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
	 * Image types used by NfN
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
	protected $accessUri = "accessURI";

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
	 * Set properties
	 *
	 * @param $workflowId
	 * @param bool $debug
	 */
	public function setProperties ($workflowId, $debug = false)
    {
		$this->states = [
            'export',
            'getStatus',
            'getResults',
            'completed',
            'analyze',
		];

		$this->imgTypes = [
			'image/jpeg' => '.jpg',
			'image/png' => '.png',
			'image/tiff' => '.tiff',
		];

		$this->setWorkflowId($workflowId);
		$this->setReportDebug($debug);

        return;
    }

	/**
	 * Set workflow id
	 *
	 * @param $workflowId
	 */
	protected function setWorkflowId ($workflowId)
	{
		$this->workflowId = $workflowId;
	}

	/**
	 * Set debug
	 *
	 * @param bool $debug
	 */
	protected function setReportDebug ($debug = false)
	{
		$this->report->setDebug($debug);
	}

    /**
     * Process current state
     *
     * @param $id
     */
    public function process($id)
    {
		$this->record = $this->expedition->findWith($id, ['project.group', 'subject.subjectDoc']);

        if (empty($this->record))
        {
            $this->report->addError(trans('errors.error_process', array('id' => $id)));
			$this->report->reportSimpleError($this->record->project->group->id);

            return;
        }

		// TODO This is set so cron does not run it every minute during presentation.
		$this->record->state = $this->record->state + 1;
		$this->expedition->save($this->record);

		try {
            $result = call_user_func(array($this, $this->states[$this->record->state]));

			if ( ! $result)
				return;
        }
        catch ( Exception $e )
        {
            $this->report->addError($e->getMessage());
			$this->report->reportSimpleError($this->record->project->group->id);
            $this->destroyDir($this->tmpFileDir);

            return;
        }

		$groupId = $this->record->project->group_id;

		// TODO Moved above to avoid cron running it every minute during presentation.
		//$this->record->state = $this->record->state+1;
		//$this->expedition->save($this->record);

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
		$title = "{$this->record->id}-" . (preg_replace('/[^a-zA-Z0-9]/', '', substr(base64_encode($this->record->title), 0, 10)));
        $this->tmpFileDir = "{$this->dataDir}/$title";

		$this->createDir($this->tmpFileDir);
		$this->writeDir($this->tmpFileDir);
		$this->buildImgDir();

        $this->processImages();

		$this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->executeCommand("tar -czf {$this->dataDir}/$title.tar.gz -C {$this->dataDir} $title");

		if (!empty($this->missingImg))
		{
			$groupId = $this->record->project->group_id;
			$this->report->missingImages($groupId, $this->record->title, $this->missingImg);
		}

		$this->createDownload($this->record->id, $this->workflowId, "$title.tar.gz");

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

			$uri = $subject->subjectDoc->subject[$this->accessUri];

			if (empty($uri))
			{
				$this->missingImg[] = $subject->id;
				continue;
			}

			$image = $this->getImage(str_replace(" ", "%20", $uri));

			if (empty($image))
			{
				$this->missingImg[] = $subject->id . ' : ' . $uri;
				continue;
			}

			$attr = getimagesizefromstring($image);

			if (!isset($this->imgTypes[$attr['mime']]))
			{
				$this->missingImg[] = $subject->id . ' : ' . $uri;
				continue;
			}

			$path = $this->tmpFileDir . '/' . $subject->id . $this->imgTypes[$attr['mime']];

			$this->saveFile($path, $image);

            $i++;
        }

		if ($i == 0)
			throw new \RuntimeException(trans('errors.error_build_image_dir', array('id' => $this->record->id)));

		return;
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
        curl_close($ch);

		return $image;
    }

    /**
     * Process images for NfN for an expedition
     */
    protected function processImages()
    {
        $data = array();

        $files = $this->filesystem->files($this->tmpFileDir);
		$lrgTargetPath = $this->tmpFileDir . '/large';
		$smTargetPath = $this->tmpFileDir . '/small';

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
		$this->metadata['highResDir'] = $lrgTargetPath;
		$this->metadata['lowResDir'] = $smTargetPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
		foreach ($files as $key => $filePath)
		{
            list($width, $height, $type, $attr) = getimagesize($filePath); // $width, $height, $type, $attr
			$sourceInfo = pathinfo($filePath); // $dirname, $basename, $extension, $filename
			$lrgTargetHeight = round(($height * $this->largeWidth) / $width);
			$lrgTargetName = "{$sourceInfo['filename']}.large.png";

			$data['identifier'] = $this->subjectArray[$sourceInfo['filename']];
			$data['original']['path'] = array($sourceInfo['filename'], ".{$sourceInfo['extension']}");
			$data['original']['name'] = $sourceInfo['basename'];
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

			$data['large']['name'] = "large/$lrgTargetName";
            $data['large']['width'] = $this->largeWidth;
			$data['large']['height'] = $lrgTargetHeight;


			$this->image->resizeImage($sourceInfo, $lrgTargetName, $lrgTargetPath, $this->largeWidth, $lrgTargetHeight);

			//$this->convertImage($filePath, $this->largeWidth, $lrgTargetHeight, $lrgImgPath);

			$smTargetHeight = round(($height * $this->smallWidth) / $width);
			$smTargetName = "{$sourceInfo['filename']}.small.png";
			$data['small']['name'] = "small/$smTargetName";
            $data['small']['width'] = $this->smallWidth;
			$data['small']['height'] = $smTargetHeight;

			$this->image->resizeImage($sourceInfo, $smTargetName, $smTargetPath, $this->smallWidth, $smTargetHeight);

			//$this->convertImage($filePath, $this->smallWidth, $smallHeight, $smImgPath);

            $this->metadata['images'][] = $data;

			$this->filesystem->delete($filePath);

            $i++;
        }
		$this->metadata['total'] = $i * 2;

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