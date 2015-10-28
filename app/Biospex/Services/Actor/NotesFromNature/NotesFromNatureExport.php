<?php

namespace Biospex\Services\Actor\NotesFromNature;

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
use Biospex\Services\Actor\ActorInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Biospex\Repo\Download\DownloadInterface;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Services\Report\Report;
use Biospex\Services\Image\Image;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use Biospex\Services\Actor\ActorAbstract;

class NotesFromNatureExport extends ActorAbstract implements ActorInterface
{
    /**
     * Actor object.
     *
     * @var object
     */
    protected $actor;

    /**
     * Expedition Id
     *
     * @var int
     */
    protected $expeditionId;

    /**
     * Current expedition being processed.
     *
     * @var object
     */
    protected $record;

    /** Notes from Nature export directory */
    protected $nfnExportDir;

    /**
     * Full path to temp directory named after expedition title with md5 hash.
     *
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
     *
     * @var string
     */
    protected $lrgFilePath;

    /**
     * Path to small images inside temp folder.
     *
     * @var string
     */
    protected $smFilePath;

    /**
     * Title of temp folder and tar file.
     *
     * @var string
     */
    public $title;

    /**
     * Array of image urls from subjects.
     *
     * @var array
     */
    protected $imageUriArray;

    /**
     * Array of existing images if files already retrieved
     *
     * @var
     */
    protected $existingImageUriArray;

    /**
     * CSV header array associated with meta file.
     *
     * @var array
     */
    protected $metaHeader = [];

    /**
     * Missing image when retrieving via curl.
     *
     * @var array
     */
    protected $missingImg = [];

    /**
     * Specific record folder for building out export.
     *
     * @var
     */
    protected $folder;

    /**
     * Large image width for NfN.
     *
     * @var int
     */
    private $largeWidth = 1540;

    /**
     * Small image width for NfN.
     *
     * @var int
     */
    private $smallWidth = 580;

    /**
     * @var ExpeditionInterface
     */
    private $expedition;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var Client
     */
    private $client;

    /**
     * Construct
     *
     * @param Filesystem $filesystem
     * @param DownloadInterface $download
     * @param ExpeditionInterface $expedition
     * @param Report $report
     * @param Image $image
     * @param Config $config
     */
    public function __construct(
        Filesystem $filesystem,
        DownloadInterface $download,
        Config $config,
        ExpeditionInterface $expedition,
        Report $report,
        Image $image
    ) {
        $this->filesystem = $filesystem;
        $this->download = $download;
        $this->config = $config;
        $this->expedition = $expedition;
        $this->report = $report;
        $this->image = $image;
        $this->client = new Client();

        $this->scratchDir = $config->get('config.scratchDir');
        $this->nfnExportDir = $config->get('config.nfnExportDir');
    }

    /**
     * Process current state
     *
     * @param $actor
     * @return mixed|void
     * @throws \Exception
     */
    public function process($actor)
    {
        $this->createDir($this->nfnExportDir);

        $this->expedition->setPass(true);
        $this->record = $this->expedition->findWith($actor->pivot->expedition_id, ['project.group', 'subjects']);

        if (empty($this->record)) {
            throw new \Exception(trans('emails.error_process', ['id' => $actor->pivot->expedition_id]));
        }

        $this->folder = "{$actor->id}-" . md5($this->record->title);

        $this->setRecordDir();

        $this->buildImageUriArray($actor);

        $requests = $this->buildImageRequests();

        $this->getImagesFromUri($requests);

        $this->convert();

        $this->splitDirectories();

        $this->buildDetails();

        $this->compressDirs();

        $this->createDownloads($actor);

        $this->moveCompressedFiles();

        $this->filesystem->deleteDirectory($this->recordDir);

        $this->processComplete();

        $actor->pivot->queued = 0;
        $actor->pivot->state = $actor->pivot->state + 1;
        $actor->pivot->completed = 1;
        $actor->pivot->save();

        return;
    }

    /**
     * Build array of image uris for curl.
     *
     * @param $actor
     * @throws \Exception
     */
    public function buildImageUriArray($actor)
    {
        foreach ($this->record->subjects as $subject) {
            if ($this->checkImageExists($subject->_id)) {
                $this->existingImageUriArray[$subject->_id] = str_replace(" ", "%20", $subject->accessURI);
                continue;
            }

            if ($this->checkUriExists($subject)) {
                continue;
            }

            $this->imageUriArray[$subject->_id] = str_replace(" ", "%20", $subject->accessURI);
        }

        if (empty($this->imageUriArray) && empty($this->existingImageUriArray)) {
            throw new \Exception(trans('emails.error_empty_image_uri', ['id' => $actor->pivot->id]));
        }

        return;
    }

    /**
     * Check if image exists
     *
     * @param $id
     * @return bool
     */
    public function checkImageExists($id)
    {
        return ! empty(glob($this->recordDir . '/' . $id . '.*')) ? true : false;
    }

    /**
     * Check if image exists
     *
     * @param $subject
     * @return bool
     */
    public function checkUriExists($subject)
    {
        if (empty($subject->accessURI)) {
            $this->addMissingImage($subject->id);

            return true;
        }

        return false;
    }

    /**
     * Create requests
     *
     * @return array
     */
    public function buildImageRequests()
    {
        if (empty($this->imageUriArray)) {
            return;
        }

        $requests = [];
        foreach ($this->imageUriArray as $key => $uri) {
            $requests[] = $this->client->createRequest('GET', $uri, ['headers' => ['key' => $key]]);
        }

        return $requests;
    }

    /**
     * Process expedition for export
     *
     * @param $requests
     */
    public function getImagesFromUri($requests)
    {
        if (empty($requests)) {
            return;
        }

        Pool::send($this->client, $requests, [
            'pool_size' => 10,
            'complete'  => function (CompleteEvent $event) {
                $url = $event->getRequest()->getUrl();
                $key = $event->getRequest()->getHeader('key');
                $code = $event->getResponse()->getStatusCode();
                $image = $event->getResponse()->getBody()->getContents();
                $this->saveImage($code, $url, $key, $image);
            },
            'error'     => function (ErrorEvent $event) {
                $this->addMissingImage($event->getRequest()->getHeader('key'), $event->getRequest()->getUrl());
            }
        ]);

        return;
    }

    /**
     * Callback function to save image
     *
     * @param $code
     * @param $url
     * @param $key
     * @param $image
     * @throws \Exception
     */
    public function saveImage($code, $url, $key, $image)
    {
        if (empty($image) || $code != 200) {
            $this->addMissingImage($key, $url);

            return;
        }

        $this->image->setImageSizeInfoFromString($image);
        $ext = $this->image->getFileExtension();

        if ( ! $ext) {
            $this->addMissingImage($key, $url);

            return;
        }

        $path = "{$this->recordDir}/$key.$ext";
        $this->saveFile($path, $image);

        return;
    }

    /**
     * Convert images.
     */
    public function convert()
    {
        $files = $this->filesystem->files($this->recordDir);

        if (empty($files)) {
            return;
        }

        $imageUriArray = $this->mergeImageUri();

        foreach ($files as $file) {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false) {
                continue;
            }

            $fileName = $this->image->getFileName();

            try {
                $this->image->imagickFile($file);
            } catch (\Exception $e) {
                $this->addMissingImage($fileName, $imageUriArray[$fileName]);
                echo "added missing image." . PHP_EOL;

                continue;
            }

            $lrgFilePath = $this->recordDirTmp . "/$fileName.large.jpg";
            $smFilePath = $this->recordDirTmp . "/$fileName.small.jpg";

            if ( ! $this->filesystem->exists($lrgFilePath)) {
                $this->image->imagickScale($lrgFilePath, $this->largeWidth, 0);
            }

            if ( ! $this->filesystem->exists($smFilePath)) {
                $this->image->imagickScale($smFilePath, $this->smallWidth, 0);
            }

            $this->image->imagickDestroy();
        }

        return;
    }

    /**
     * Returns image uri array
     * @return array
     */
    protected function mergeImageUri()
    {
        if ( ! empty($this->imageUriArray) && ! empty($this->existingImageUriArray)) {
            return array_merge($this->imageUriArray, $this->existingImageUriArray);
        }

        if (empty($this->imageUriArray) && ! empty($this->existingImageUriArray)) {
            return $this->existingImageUriArray;
        }

        if ( ! empty($this->imageUriArray) && empty($this->existingImageUriArray)) {
            return $this->imageUriArray;
        }

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

        foreach ($files as $file) {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false) {
                continue;
            }

            $fileName = $this->image->getFileName();
            $baseName = $this->image->getBaseName();

            $lrgFilePath = $this->recordDirTmp . "/$fileName.large.jpg";
            $smFilePath = $this->recordDirTmp . "/$fileName.small.jpg";

            $size += filesize($lrgFilePath);
            $size += filesize($smFilePath);

            $this->filesystem->move($lrgFilePath, $this->lrgFilePath . "/$fileName.large.jpg");
            $this->filesystem->move($smFilePath, $this->smFilePath . "/$fileName.small.jpg");
            $this->filesystem->move($file, $this->splitDir . "/$baseName");

            if ($size >= $limit) {
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

        foreach ($directories as $directory) {
            $data = [];
            $metadata['total'] = 0;
            $metadata['images'] = [];

            $files = $this->filesystem->files($directory);

            $i = 0;
            foreach ($files as $file) {
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

            $this->saveFile("$directory/details.js",
                json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return;
    }

    /**
     * Set tmp directory used.
     */
    public function setRecordDir()
    {
        $this->recordDir = $this->scratchDir . '/' . $this->folder;
        $this->recordDirTmp = $this->recordDir . '/tmp';
        $this->createDir($this->recordDirTmp);
        $this->writeDir($this->recordDirTmp);

        return;
    }

    public function setSplitDir()
    {
        $count = ++$this->count;
        $this->splitDir = $this->recordDir . '/' . $this->folder . '-' . $count;
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
     * Add missing image information to array.
     *
     * @param $key
     * @param $uri
     */
    public function addMissingImage($key = null, $uri = null)
    {
        if ( ! is_null($key) && ! is_null($uri)) {
            $this->missingImg[] = ['value' => $key . ' : ' . $uri];
        }

        if (is_null($key) && ! is_null($uri)) {
            $this->missingImg[] = ['value' => $uri];
        }

        if ( ! is_null($key) && is_null($uri)) {
            $this->missingImg[] = ['value' => $key];
        }

        return;
    }

    /**
     * Compress directory.
     */
    public function compressDirs()
    {
        $directories = $this->filesystem->directories($this->recordDir);
        foreach ($directories as $directory) {
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
     *
     * @param $actor
     */
    public function createDownloads($actor)
    {
        $files = $this->filesystem->files($this->recordDir);
        foreach ($files as $file) {
            $baseName = pathinfo($file, PATHINFO_BASENAME);
            $this->createDownload($this->record->id, $actor->id, $baseName);
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
        foreach ($files as $file) {
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

        $gb = 1073741824;

        return ($size < $gb) ? $size : ceil($size / ceil(number_format($size / $gb, 2)));
    }

    /**
     * Report complete process.
     */
    protected function processComplete()
    {
        $group_id = $this->record->project->group_id;
        $title = $this->record->title;
        $missingImg = $this->missingImg;
        $name = $this->record->id . '-missing_images';

        $this->report->processComplete($group_id, $title, $missingImg, $name);

    }
}
