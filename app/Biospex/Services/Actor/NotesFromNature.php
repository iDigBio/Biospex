<?php namespace Biospex\Services\Actor;
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

class NotesFromNature extends ActorAbstract
{
    /**
     * @var array
     */
    protected $states = array();

	/**
	 * Actor object
	 */
	protected $actor;

	/**
	 * Expedition Id
	 */
	protected $expeditionId;

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
    protected $identifierArray;

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
	 * @param $actor
	 */
	public function setProperties ($actor)
    {
		$this->states = [
            'export',
            'getStatus',
            'getResults',
            'completed',
            'analyze',
		];

		$this->actor = $actor;
		$this->expeditionId = $actor->pivot->expedition_id;

        return;
    }

    /**
     * Process current state
     */
    public function process()
    {
		$this->expedition->setPass(true);
		$this->record = $this->expedition->findWith($this->expeditionId, ['project.group', 'subjects']);

        if (empty($this->record))
        {
            $this->report->addError(trans('emails.error_process', array('id' => $this->expeditionId)));
			$this->report->reportSimpleError($this->record->project->group->id);

            return;
        }

		try {
            $result = call_user_func(array($this, $this->states[$this->actor->pivot->state]));

			if ( ! $result)
				return;
        }
        catch ( Exception $e )
        {
            $this->report->addError($e->getMessage());
			$this->report->reportSimpleError($this->record->project->group->id);
			$this->filesystem->deleteDirectory($this->tmpFileDir);

            return;
        }

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
		$title = "{$this->record->id}-" . (preg_replace('/[^a-zA-Z0-9]/', '', substr(md5(uniqid(mt_rand(), true)), 0, 10)));
        $this->tmpFileDir = "{$this->dataDir}/$title";

		$this->createDir($this->tmpFileDir);
		$this->writeDir($this->tmpFileDir);

        \Log::alert("Building image directory for {$this->record->id}");
		$this->buildImgDir();
        \Log::alert("Finished building image directory for {$this->record->id}");

        \Log::alert("Start processing images for {$this->record->id}");
        $this->processImages();
        \Log::alert("Processed images for {$this->record->id}");

        \Log::alert("Saving details file for {$this->record->id}");
		$this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        \Log::alert("Saved details file for {$this->record->id}");

        \Log::alert("Executing tar command for {$this->record->id}");
        $this->executeCommand("tar -czf {$this->dataDir}/$title.tar.gz {$this->tmpFileDir}");
        \Log::alert("Executed tar file for {$this->record->id}");

		if (!empty($this->missingImg))
		{
			$groupId = $this->record->project->group_id;
			$this->report->missingImages($groupId, $this->record->title, $this->missingImg);
		}

		$this->createDownload($this->record->id, $this->actor->id, "$title.tar.gz");

		$this->filesystem->deleteDirectory($this->tmpFileDir);

		$groupId = $this->record->project->group_id;

		$this->actor->pivot->state = $this->actor->pivot->state+1;
		$this->actor->pivot->save();

		$this->report->processComplete($groupId, $this->record->title);

        return true;
    }

    /**
     * Process expedition for export
     */
    protected function buildImgDir()
    {
        $i = 0;
		foreach ($this->record->subjects as $subject)
        {
			// Sets up array for retrieving identifier when building details.js
            $this->identifierArray[$subject->_id] = $subject->id;

			$uri = $subject->{$this->accessUri};

			if (empty($uri))
			{
				$this->missingImg[] = $subject->id;
				continue;
			}

			$image = $this->image->getImageFromUrl($uri);

			if (empty($image))
			{
				$this->missingImg[] = $subject->id . ' : ' . $uri;
				continue;
			}

			$ext = $this->image->getExtension($image, true);

			if ( ! $ext)
			{
				$this->missingImg[] = $subject->id . ' : ' . $uri;
				continue;
			}

			$path = $this->tmpFileDir . '/' . $subject->_id . $ext;

			$this->saveFile($path, $image);

            $i++;
        }

		if ($i == 0)
			throw new \RuntimeException(trans('emails.error_build_image_dir', array('id' => $this->record->id)));

		return;
    }

    /**
     * Process images for NfN for an expedition
     */
    protected function processImages()
    {
        $data = array();

        $files = $this->filesystem->files($this->tmpFileDir);

        $lrgTargetPath = $this->tmpFileDir . '/large';
        $this->image->createDirectory($lrgTargetPath);

        $smTargetPath = $this->tmpFileDir . '/small';
        $this->image->createDirectory($smTargetPath);

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
		$this->metadata['highResDir'] = $lrgTargetPath;
		$this->metadata['lowResDir'] = $smTargetPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        \Log::alert("Looping through images");
        $i = 0;
		foreach ($files as $key => $filePath)
		{
            \Log::alert("Getting image size");
            list($width, $height) = getimagesize($filePath); // $width, $height, $type, $attr

            \Log::alert("Getting image path info");
            $sourceInfo = pathinfo($filePath); // $dirname, $basename, $extension, $filename
			$sourceFilePath = $sourceInfo['dirname'] . '/' . $sourceInfo['basename'];

            \Log::alert("Setting image proportion");
			$lrgTargetHeight = $this->setProportion($width, $height, $this->largeWidth);
			$lrgTargetName = "{$sourceInfo['filename']}.large.png";
			$targetFilePathLg = $lrgTargetPath . '/' . $lrgTargetName;

			$smTargetHeight = $this->setProportion($width, $height, $this->smallWidth);
			$smTargetName = "{$sourceInfo['filename']}.small.png";
			$targetFilePathSm = $smTargetPath . '/' . $smTargetName;

			// Set array
			$data['identifier'] = $this->identifierArray[$sourceInfo['filename']];
			$data['original']['path'] = [$sourceInfo['filename'], ".{$sourceInfo['extension']}"];
			$data['original']['name'] = $sourceInfo['basename'];
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

			$data['large']['name'] = "large/$lrgTargetName";
            $data['large']['width'] = $this->largeWidth;
			$data['large']['height'] = $lrgTargetHeight;

			$data['small']['name'] = "small/$smTargetName";
			$data['small']['width'] = $this->smallWidth;
			$data['small']['height'] = $smTargetHeight;

            \Log::alert("Setting image and resizing {$filePath}");
			$this->image->setWidth($this->largeWidth);
			$this->image->setHeight($lrgTargetHeight);
			$this->image->resizeImage($sourceFilePath, $targetFilePathLg);

			$this->image->setWidth($this->smallWidth);
			$this->image->setHeight($smTargetHeight);
			$this->image->resizeImage($sourceFilePath, $targetFilePathSm);
            \Log::alert("Finished setting image and resizing {$filePath}");

            $this->metadata['images'][] = $data;

            \Log::alert("Deleting {$filePath}");
			if ( ! $this->filesystem->delete($filePath))
                Log::error('Failed to delete image: ' . $filePath);

            $i++;
        }

        \Log::alert("Finshed image loop");

		$this->metadata['total'] = $i * 2;

        return;
    }

	protected function setProportion($width, $height, $limit)
	{
		return round(($height * $limit) / $width);
	}
}