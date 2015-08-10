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
use Illuminate\Support\Facades\Config;

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

    /** Notes from Nature export directory */
    protected $nfnExportDir;

    /**
     * Full path to temp directory named after expedition title with md5 hash.
     * @var string
     */
    protected $recordDir;

    /**
     * Full path to temp directory inside $recordDir for image conversions.
     *
     * @var
     */
    protected $recordDirTmp;

    /**
     * Directories built based on 1GB file sizes or less.
     *
     * @var
     */
    protected $splitDir;

    /**
     * Count of chunk directory.
     *
     * @var
     */
    protected $count = 0;

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
        $this->nfnExportDir = Config::get('config.nfnExportDir');
        $this->createDir($this->nfnExportDir);

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

        $this->actor->pivot->state = $this->actor->pivot->state + 1;
        $this->actor->pivot->save();

        $this->report->processComplete($this->record->project->group_id, $this->record->title, $this->missingImg, $this->record->id . '-missing_images');

        return;
    }

    /**
     * Export the expedition
     */
    public function export()
    {
        $this->setTitle("{$this->record->id}-" . md5($this->record->title));

        $this->setRecordDir();

        $this->buildImageUriArray();

        $this->getImagesFromUri();

        $this->convert();

        $this->splitDirectories();

        $this->buildDetails();

        $this->compressDirs();

        $this->createDownloads();

        $this->moveCompressedFiles();

        $this->filesystem->deleteDirectory($this->recordDir);

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

            $this->imageUriArray[$subject->_id] = str_replace(" ", "%20", $uri);
        }

        if (empty($this->imageUriArray))
            throw new \Exception(trans('emails.error_empty_image_uri', ['id' => $this->expeditionId]));

        return;
    }

    /**
     * Process expedition for export
     */
    public function getImagesFromUri()
    {
        $rc = new Curl([$this, "saveImage"]);
        $rc->options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLINFO_HEADER_OUT => 1];

        $execute = false;
        foreach ($this->imageUriArray as $key => $uri)
        {
            $result = glob("{$this->recordDir}/$key.*");
            if ( ! empty($result))
                continue;

            $rc->get($uri, ["key: $key"]);
            $execute = true;
        }

        if ($execute)
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

            $this->image->setImageSizeInfoFromString($image);
            $ext = $this->image->getFileExtension();

            if ( ! $ext)
            {
                $this->addMissingImage($key, $info['url']);

                return;
            }

            $path = "{$this->recordDir}/$key.$ext";
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
        $files = $this->filesystem->files($this->recordDir);

        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false)
                continue;

            $fileName = $this->image->getFileName();

            try
            {

                $this->image->imagickFile($file);
            }
            catch (\Exception $e)
            {
                $this->addMissingImage($fileName, $this->imageUriArray[$fileName]);

                continue;
            }

            $lrgFilePath = $this->recordDirTmp . "/$fileName.large.jpg";
            $smFilePath = $this->recordDirTmp. "/$fileName.small.jpg";

            if ( ! $this->filesystem->exists($lrgFilePath))
                $this->image->imagickScale($lrgFilePath, $this->largeWidth, 0);

            if ( ! $this->filesystem->exists($smFilePath))
                $this->image->imagickScale($smFilePath, $this->smallWidth, 0);

            $this->image->imagickDestroy();
        }

        return;

    }

    /**
     * Split tmp directory into separate directories based on size.
     */
    public function splitDirectories()
    {
        $size = 0;
        $this->setSplitDir();
        $limit = $this->getDirectorySize();
        $files = $this->filesystem->files($this->recordDir);

        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false)
                continue;

            $fileName = $this->image->getFileName();
            $baseName = $this->image->getBaseName();

            $lrgFilePath = $this->recordDirTmp . "/$fileName.large.jpg";
            $smFilePath = $this->recordDirTmp. "/$fileName.small.jpg";

            $size += filesize($lrgFilePath);
            $size += filesize($smFilePath);

            $this->filesystem->move($lrgFilePath, $this->lrgFilePath . "/$fileName.large.jpg");
            $this->filesystem->move($smFilePath, $this->smFilePath . "/$fileName.small.jpg");
            $this->filesystem->move($file, $this->splitDir . "/$baseName");

            if ($size >= $limit)
            {
                $this->setSplitDir();
                $size = 0;
            }
        }

        $this->filesystem->deleteDirectory($this->recordDirTmp);

        return;
    }

    /**
     * Build detail.js file.
     */
    public function buildDetails()
    {
        $directories = $this->filesystem->directories($this->recordDir);

        $metadata = [];
        $metadata['sourceDir'] = $this->recordDir;
        $metadata['targetDir'] = $this->recordDir;
        $metadata['created_at'] = date('l jS F Y', time());
        $metadata['highResDir'] = $this->recordDir . '/large';
        $metadata['lowResDir'] = $this->recordDir . '/small';
        $metadata['highResWidth'] = $this->largeWidth;
        $metadata['lowResWidth'] = $this->smallWidth;

        foreach ($directories as $directory)
        {
            $data = [];
            $metadata['total'] = 0;
            $metadata['images'] = [];

            $files = $this->filesystem->files($directory);

            $i = 0;
            foreach ($files as $file)
            {
                // Original Image info.
                $this->image->setImagePathInfo($file);
                $baseName = $this->image->getBaseName();
                $fileName = $this->image->getFileName();
                $extension = $this->image->getFileExtension();
                $this->image->setImageSizeInfoFromFile($file);

                // Set array for original image.
                $data['identifier'] = $fileName;
                $data['original']['path'] = [$fileName, ".$extension"];
                $data['original']['name'] = $baseName;
                $data['original']['width'] = $this->image->getImageWidth();
                $data['original']['height'] = $this->image->getImageHeight();

                // Set array for large image.
                $this->image->setImageSizeInfoFromFile("$directory/large/$fileName.large.$extension");
                $data['large']['name'] = "large/$fileName.large.$extension";
                $data['large']['width'] = $this->image->getImageWidth();
                $data['large']['height'] = $this->image->getImageHeight();

                // Set array for small image.
                $this->image->setImageSizeInfoFromFile("$directory/small/$fileName.small.$extension");
                $data['small']['name'] = "small/$fileName.small.$extension";
                $data['small']['width'] = $this->image->getImageWidth();
                $data['small']['height'] = $this->image->getImageHeight();

                $metadata['images'][] = $data;

                $this->filesystem->delete($file);

                $i++;
            }

            $metadata['total'] = $i * 2;

            $this->saveFile("$directory/details.js", json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        }

        return;
    }

    /**
     * Set tmp directory used.
     */
    public function setRecordDir()
    {
        $this->recordDir = $this->scratchDir . '/' . $this->title;
        $this->recordDirTmp = $this->recordDir . '/tmp';
        $this->createDir($this->recordDirTmp);
        $this->writeDir($this->recordDirTmp);

        return;
    }

    public function setSplitDir()
    {
        $count = ++$this->count;
        $this->splitDir = $this->recordDir . '/' . $this->title . '-' . $count;
        $this->createDir($this->splitDir);
        $this->writeDir($this->splitDir);

        $this->lrgFilePath = $this->splitDir . '/large';
        $this->createDir($this->lrgFilePath);
        $this->writeDir($this->lrgFilePath);

        $this->smFilePath = $this->splitDir . '/small';
        $this->createDir($this->smFilePath);
        $this->writeDir($this->smFilePath);

        return;
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
            $this->missingImg[] = ['value' => $key . ' : ' . $uri];

        if (is_null($key) && ! is_null($uri))
            $this->missingImg[] = ['value' => $uri];

        if ( ! is_null($key) && is_null($uri))
            $this->missingImg[] = ['value' => $key];

        return;
    }

    /**
     * Compress directory.
     */
    public function compressDirs()
    {
        $directories = $this->filesystem->directories($this->recordDir);
        foreach ($directories as $directory)
        {
            $a = new \PharData("$directory.tar");
            $a->buildFromDirectory($directory);
            $a->compress(\Phar::GZ);
            unset($a);
            $this->filesystem->delete("$directory.tar");
            $this->filesystem->deleteDirectory($directory);
        }

        return;
    }

    /**
     * Add download files to downloads table.
     */
    public function createDownloads()
    {
        $files = $this->filesystem->files($this->recordDir);
        foreach ($files as $file)
        {
            $baseName = pathinfo($file, PATHINFO_BASENAME);
            $this->createDownload($this->record->id, $this->actor->id, $baseName);
        }

        return;
    }

    /**
     * Move tar files to export folder.
     *
     * @throws \Exception
     */
    public function moveCompressedFiles()
    {
        $files = $this->filesystem->files($this->recordDir);
        foreach ($files as $file)
        {
            $baseName = pathinfo($file, PATHINFO_BASENAME);
            $this->moveFile($file, "{$this->nfnExportDir}/$baseName");
        }

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

    /**
     * Set directory sizes for download files.
     *
     * @return float
     */
    protected function getDirectorySize()
    {
        exec("du -b -s {$this->recordDirTmp}", $op);
        list($size) = preg_split('/\s+/', $op[0]);

        return ceil($size/ceil(number_format($size / 1073741824, 2)));
    }
}
