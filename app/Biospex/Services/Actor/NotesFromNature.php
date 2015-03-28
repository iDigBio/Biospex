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
use Biospex\Services\Curl\Curl;

class NotesFromNature extends ActorAbstract {

    /**
     * @var array
     */
    protected $states = [];

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
     * Path to large images inside temp folder
     * @var
     */
    protected $lrgTargetPath;

    /**
     * Path to small images inside temp folder
     * @var
     */
    protected $smTargetPath;

    /**
     * Title of temp folder and tar file
     * @var
     */
    protected $title;

    /**
     * Array of image urls from subjects.
     *
     * @var $imageUriArray
     */
    protected $imageUriArray;

    /**
     * CSV header array associated with meta file
     * @var array
     */
    protected $metaHeader = [];

    /**
     * Remote image column from csv import
     * @var
     */
    protected $accessURI = "accessURI";

    /**
     * Missing image when retrieving via curl
     *
     * @var array
     */
    protected $missingImg = [];

    /**
     * Data array for images
     * @var array
     */
    protected $data = [];

    /**
     * Metadata array for images
     * @var array
     */
    protected $metadata = [];

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
    private $smallWidth = 580;

    /**
     * Set properties
     *
     * @param $actor
     */
    public function setProperties($actor)
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
            $this->report->addError(trans('emails.error_process', ['id' => $this->expeditionId]));
            $this->report->reportSimpleError($this->record->project->group->id);

            return;
        }

        if ( ! is_callable([$this, $this->states[$this->actor->pivot->state]]))
            return;

        call_user_func([$this, $this->states[$this->actor->pivot->state]]);

        return;
    }

    /**
     * Export the expedition
     *
     * @throws \RuntimeException
     */
    public function export()
    {
        $this->setPaths();

        $this->buildImageUriArray();

        $this->getImagesFromUri();

        $this->buildFiles();

        $this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->compressDir();

        if ( ! empty($this->missingImg))
        {
            $groupId = $this->record->project->group_id;
            $this->report->missingImages($groupId, $this->record->title, $this->missingImg);
        }

        $this->createDownload($this->record->id, $this->actor->id, "{$this->title}.tar.gz");

        $this->filesystem->deleteDirectory($this->tmpFileDir);

        $groupId = $this->record->project->group_id;

        $this->actor->pivot->state = $this->actor->pivot->state + 1;
        $this->actor->pivot->save();

        $this->report->processComplete($groupId, $this->record->title);

        return true;
    }

    /**
     * Build array of image uris for curl.
     */
    public function buildImageUriArray()
    {
        foreach ($this->record->subjects as $subject)
        {
            $uri = $subject->{$this->accessURI};
            if (empty($uri))
            {
                $this->addMissingImage($subject->id);
                continue;
            }

            // Sets up array for retrieving identifier when building details.js
            $this->identifierArray[$subject->_id] = $subject->id;

            $this->imageUriArray[$subject->_id] = str_replace(" ", "%20", $uri);
        }

        return;
    }

    /**
     * Process expedition for export
     */
    public function getImagesFromUri()
    {
        $rc = new Curl([$this, "saveImage"]);
        $rc->options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLINFO_HEADER_OUT => 1];
        $rc->window_size = 5;

        foreach ($this->imageUriArray as $key => $uri)
        {
            $rc->get($uri, ["key: $key"]);
        }

        $rc->execute();

        return;
    }

    public function saveImage($image, $info)
    {
        if ($info['http_code'] == 200)
        {
            $key = $this->getImageKey($info['request_header']);

            if (empty($image))
            {
                $this->addMissingImage($key, $info['url']);

                return;
            }

            $ext = $this->image->getExtensionFromString($image);

            if ( ! $ext)
            {
                $this->addMissingImage($key, $info['url']);

                return;
            }

            $path = "{$this->tmpFileDir}/$key.$ext";

            $this->saveFile($path, $image);

            return;
        }

        $this->addMissingImage($info['url']);

        return;
    }

    /**
     * Process images for NfN for an expedition
     */
    public function buildFiles()
    {
        $data = [];

        $files = $this->filesystem->files($this->tmpFileDir);

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
        $this->metadata['highResDir'] = $this->lrgTargetPath;
        $this->metadata['lowResDir'] = $this->smTargetPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
        foreach ($files as $file)
        {
            $this->image->imageMagick($file);
            $origWidth = $this->image->getImageWidth();
            $origHeight = $this->image->getImageHeight();
            $baseName = $this->image->getBaseName();
            $fileName = $this->image->getFileName();
            $extension = $this->image->getExtension();

            $lrgTargetName = "$fileName.large.$extension";
            $targetFilePathLg = $this->lrgTargetPath . '/' . $lrgTargetName;
            $this->image->resize($targetFilePathLg, $this->largeWidth, 0);

            $smTargetName = $lrgTargetName = "$fileName.small.$extension";
            $targetFilePathSm = $this->smTargetPath . '/' . $smTargetName;
            $this->image->resize($targetFilePathSm, $this->smallWidth, 0);

            $this->image->destroy();

            // Set array
            $data['identifier'] = $this->identifierArray[$fileName];
            $data['original']['path'] = [$fileName, ".$extension"];
            $data['original']['name'] = $baseName;
            $data['original']['width'] = $origWidth;
            $data['original']['height'] = $origHeight;

            $data['large']['name'] = "large/$lrgTargetName";
            $data['large']['width'] = $this->largeWidth;
            $data['large']['height'] = $this->image->getImageSizeFromFile($targetFilePathLg, 'h');

            $data['small']['name'] = "small/$smTargetName";
            $data['small']['width'] = $this->smallWidth;
            $data['small']['height'] = $this->image->getImageSizeFromFile($targetFilePathSm, 'h');;

            $this->metadata['images'][] = $data;

            $this->filesystem->delete($file);

            \Log::alert("Saved $file");

            $i++;
        }

        $this->metadata['total'] = $i * 2;

        return;
    }

    /**
     * Set paths used.
     */
    public function setPaths()
    {
        $this->title = "{$this->record->id}-" . (preg_replace('/[^a-zA-Z0-9]/', '', substr(md5(uniqid(mt_rand(), true)), 0, 10)));
        $this->tmpFileDir = "{$this->dataDir}/$this->title";
        $this->createDir($this->tmpFileDir);
        $this->writeDir($this->tmpFileDir);

        $this->lrgTargetPath = $this->tmpFileDir . '/large';
        $this->createDir($this->lrgTargetPath);
        $this->writeDir($this->lrgTargetPath);

        $this->smTargetPath = $this->tmpFileDir . '/small';
        $this->createDir($this->smTargetPath);
        $this->writeDir($this->smTargetPath);
    }

    /**
     * Add missing image information to array.
     *
     * @param $key
     * @param $uri
     */
    public function addMissingImage($key, $uri = null)
    {
        $this->missingImg[] = $key . ' : ' . $uri;

        return;
    }

    /**
     * Compress directory.
     */
    public function compressDir()
    {
        $a = new \PharData("{$this->dataDir}/{$this->title}.tar");
        $a->buildFromDirectory("{$this->dataDir}/{$this->title}");
        $a->compress(\Phar::GZ);
        unset($a);
        unlink("{$this->dataDir}/{$this->title}.tar");

        return;
    }

    /**
     * Get image key from headers.
     *
     * @param $headers
     * @return mixed
     */
    public function getImageKey($headers)
    {
        $header = $this->parseHeader($headers);

        return $header['key'];
    }
}
