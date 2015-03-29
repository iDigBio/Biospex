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
     * States of expedition corresponding to class methods.
     * @var array
     */
    protected $states = [];

    /**
     * Actor object.
     * @var object
     */
    protected $actor;

    /**
     * Expedition Id
     * @var int
     */
    protected $expeditionId;

    /**
     * Current expedition being processed.
     * @var object
     */
    protected $record;

    /**
     * Data Directory.
     * @var string
     */
    protected $dataDir;

    /**
     * Full path to temp file director.
     * @var string
     */
    protected $tmpFileDir;

    /**
     * Path to large images inside temp folder.
     * @var string
     */
    protected $lrgFilePath;

    /**
     * Path to small images inside temp folder.
     * @var string
     */
    protected $smFilePath;

    /**
     * Title of temp folder and tar file.
     * @var string
     */
    public $title;

    /**
     * Array of image urls from subjects.
     * @var array
     */
    protected $imageUriArray;

    /**
     * CSV header array associated with meta file.
     * @var array
     */
    protected $metaHeader = [];

    /**
     * Remote image column from csv import.
     * @var string
     */
    protected $accessURI = "accessURI";

    /**
     * Missing image when retrieving via curl.
     * @var array
     */
    protected $missingImg = [];

    /**
     * Data array for images.
     * @var array
     */
    protected $data = [];

    /**
     * Metadata array for images.
     * @var array
     */
    protected $metadata = [];

    /**
     * Array to hold subjects and identifiers.
     * @var array
     */
    protected $identifierArray;

    /**
     * Large image width for NfN.
     * @var int
     */
    private $largeWidth = 1540;

    /**
     * Small image width for NfN.
     * @var int
     */
    private $smallWidth = 580;

    /**
     * Set properties
     * @param $actor
     * @return mixed
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
     */
    public function export()
    {
        $this->setTitle("{$this->record->id}-" . md5($this->record->title));

        $this->setPaths();

        $this->buildImageUriArray();

        $this->getImagesFromUri();

        $this->convert();

        $this->buildDetails();

        $this->compressDir();

        $this->createDownload($this->record->id, $this->actor->id, "{$this->title}.tar.gz");

        $this->filesystem->deleteDirectory($this->tmpFileDir);

        $groupId = $this->record->project->group_id;

        $this->actor->pivot->state = $this->actor->pivot->state + 1;
        $this->actor->pivot->save();

        $this->report->processComplete($groupId, $this->record->title, $this->missingImg, $this->record->id . '-missing_images');

        return;
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

    /**
     * Callback function to save retrieved image from curl.
     *
     * @param $image
     * @param $info
     * @throws \Exception
     */
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

            $ext = $this->image->getImageExtensionFromString($image);

            if ( ! $ext)
            {
                $this->addMissingImage($key, $info['url']);

                return;
            }

            $path = "{$this->tmpFileDir}/$key.$ext";
            $this->saveFile($path, $image);

            return;
        }

        $this->addMissingImage(null, $info['url']);

        return;
    }

    /**
     * Convert images.
     */
    public function convert()
    {
        $files = $this->filesystem->files($this->tmpFileDir);
        $i=0;
        foreach ($files as $file)
        {
            $this->image->imageMagick($file);
            $fileName = $this->image->getFileName();
            $extension = $this->image->getExtension();

            $lrgFilePath = "{$this->lrgFilePath}/$fileName.large.$extension";
            $smFilePath = "{$this->smFilePath}/$fileName.small.$extension";

            if ($this->filesystem->exists($lrgFilePath) && $this->filesystem->exists($smFilePath))
                continue;

            if ( ! $this->image->resize($lrgFilePath, $this->largeWidth, 0))
            {
                $this->filesystem->delete($file);
                $this->addMissingImage(null, $lrgFilePath);
            }
            elseif ( ! $this->image->resize($smFilePath, $this->smallWidth, 0))
            {
                $this->filesystem->delete($file);
                $this->addMissingImage(null, $smFilePath);
            }

            $this->image->destroy();
            $i++;

            if ($i % 50 == 0)
                \Log::alert("Converted $i");
        }

        return;

    }

    /**
     * Build detail.js file.
     */
    public function buildDetails()
    {
        $data = [];

        $files = $this->filesystem->files($this->tmpFileDir);

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
        $this->metadata['highResDir'] = $this->lrgFilePath;
        $this->metadata['lowResDir'] = $this->smFilePath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
        foreach ($files as $file)
        {
            // Original Image info.
            list($width, $height) = $this->image->getImageSizeFromFile($file);
            $this->image->setImagePathInfo($file);
            $baseName = $this->image->getBaseName();
            $fileName = $this->image->getFileName();
            $extension = $this->image->getExtension();

            // Set array for original image.
            $data['identifier'] = $this->identifierArray[$fileName];
            $data['original']['path'] = [$fileName, ".$extension"];
            $data['original']['name'] = $baseName;
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

            // Set array for large image.
            list($width, $height) = $this->image->getImageSizeFromFile("{$this->lrgFilePath}/$fileName.large.$extension");
            $data['large']['name'] = "large/$fileName.large.$extension";
            $data['large']['width'] = $width;
            $data['large']['height'] = $height;

            // Set array for small image.
            list($width, $height) = $this->image->getImageSizeFromFile("{$this->smFilePath}/$fileName.small.$extension");
            $data['small']['name'] = "small/$fileName.small.$extension";
            $data['small']['width'] = $width;
            $data['small']['height'] = $height;

            $this->metadata['images'][] = $data;

            $this->filesystem->delete($file);

            $i++;
        }

        $this->metadata['total'] = $i * 2;

        $this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return;
    }

    /**
     * Set paths used.
     */
    public function setPaths()
    {
        $this->tmpFileDir = "{$this->dataDir}/$this->title";
        $this->createDir($this->tmpFileDir);
        $this->writeDir($this->tmpFileDir);

        $this->lrgFilePath = $this->tmpFileDir . '/large';
        $this->createDir($this->lrgFilePath);
        $this->writeDir($this->lrgFilePath);

        $this->smFilePath = $this->tmpFileDir . '/small';
        $this->createDir($this->smFilePath);
        $this->writeDir($this->smFilePath);
    }

    /**
     * Set title for image directory.
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Add missing image information to array.
     *
     * @param $key
     * @param $uri
     */
    public function addMissingImage($key = null, $uri = null)
    {
        if ( ! is_null($key) && ! is_null($uri))
            $this->missingImg[] = $key . ' : ' . $uri;

        if (is_null($key) && ! is_null($uri))
            $this->missingImg[] = $uri;

        if ( ! is_null($key) && is_null($uri))
            $this->missingImg[] = $key;

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
