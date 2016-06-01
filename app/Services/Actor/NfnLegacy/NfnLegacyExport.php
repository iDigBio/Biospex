<?php

namespace App\Services\Actor\NfnLegacy;

ini_set('memory_limit', '1024M');

use Illuminate\Config\Repository as Config;
use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\Expedition;
use App\Services\Report\Report;
use App\Services\Image\Image;
use App\Services\Actor\ActorAbstract;
use App\Services\Actor\ActorInterface;
use Illuminate\Filesystem\Filesystem;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;

class NfnLegacyExport extends ActorAbstract implements ActorInterface
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
     * @var Expedition
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
     * NotesFromNatureOrigExport constructor.
     * @param Filesystem $filesystem
     * @param Download $download
     * @param Config $config
     * @param Expedition $expedition
     * @param Report $report
     * @param Image $image
     */
    public function __construct(
        Filesystem $filesystem,
        Download $download,
        Config $config,
        Expedition $expedition,
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

        $this->scratchDir = $config->get('config.scratch_dir');
        $this->nfnExportDir = $config->get('config.nfn_export_dir');
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

        $this->record = $this->expedition->skipCache()->with(['project.group', 'subjects'])->find($actor->pivot->expedition_id);

        if (empty($this->record)) {
            throw new \Exception(trans('emails.error_process', ['id' => $actor->pivot->expedition_id]));
        }

        $this->folder = "{$actor->id}-" . md5($this->record->title);

        $this->setRecordDir();

        $this->buildImageUriArray($actor);

        $this->getImagesFromUri();

        $this->convert();

        $this->splitDirectories();

        $this->buildDetails();

        $this->compressDirs();

        $this->createDownloads($actor);

        $this->moveCompressedFiles();

        $this->filesystem->deleteDirectory($this->recordDir);

        $this->processComplete();

        $actor->pivot->queued = 0;
        ++$actor->pivot->state;
        $actor->pivot->completed = 1;
        $actor->pivot->save();
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
                $this->existingImageUriArray[$subject->_id] = str_replace(' ', '%20', $subject->accessURI);
                continue;
            }

            if ($this->checkUriExists($subject)) {
                continue;
            }

            $this->imageUriArray[$subject->_id] = str_replace(' ', '%20', $subject->accessURI);
        }

        if (empty($this->imageUriArray) && empty($this->existingImageUriArray)) {
            throw new \Exception(trans('emails.error_empty_image_uri', ['id' => $actor->pivot->id]));
        }

    }

    /**
     * Check if image exists
     *
     * @param $id
     * @return bool
     */
    public function checkImageExists($id)
    {
        return count($this->filesystem->glob($this->recordDir . '/' . $id . '.*')) > 0;
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
     * Process expedition for export
     */
    public function getImagesFromUri()
    {
        if (empty($this->imageUriArray)) {
            return;
        }

        $requests = function ($uriArray) {
            foreach ($uriArray as $index => $url) {
                yield $index => new Request('GET', $url);
            }
        };

        $pool = new Pool($this->client, $requests($this->imageUriArray), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                $code = $response->getStatusCode();
                $image = $response->getBody();
                $this->saveImage($code, $index, $image);
            },
            'rejected' => function ($reason, $index) {
                $this->addMissingImage($index, $this->imageUriArray[$index]);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Callback function to save image
     * @param $code
     * @param $index
     * @param $image
     * @throws \Exception
     */
    public function saveImage($code, $index, $image)
    {
        if (empty($image) || $code != 200) {
            $this->addMissingImage($index, $this->imageUriArray[$index]);

            return;
        }

        $this->image->setImageSizeInfoFromString($image);
        $ext = $this->image->getFileExtension();

        if ( ! $ext) {
            $this->addMissingImage($index, $this->imageUriArray[$index]);

            return;
        }

        $path = "{$this->recordDir}/$index.$ext";
        $this->saveFile($path, $image);
    }

    /**
     * Convert images.
     */
    public function convert()
    {
        $files = $this->filesystem->files($this->recordDir);

        if (count($files) === 0) {
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

    }

    /**
     * Returns image uri array
     * @return array
     */
    protected function mergeImageUri()
    {
        $imageUriCount = count($this->imageUriArray);
        $existingImageUriCount = count($this->existingImageUriArray);
        
        if ($imageUriCount !== 0 && $existingImageUriCount !== 0) {
            return array_merge($this->imageUriArray, $this->existingImageUriArray);
        }

        if ($imageUriCount === 0 && $existingImageUriCount !== 0) {
            return $this->existingImageUriArray;
        }

        if ($imageUriCount !== 0 && $existingImageUriCount === 0) {
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
    }

    /**
     * Add missing image information to array
     * @param null $index
     * @param null $uri
     */
    public function addMissingImage($index = null, $uri = null)
    {
        if (($index !== null) && !($uri !== null)) {
            $this->missingImg[] = ['value' => $index . ' : ' . $uri];
        }

        if (($index === null) && ($uri !== null)) {
            $this->missingImg[] = ['value' => $uri];
        }

        if (($index !== null) && ($uri === null)) {
            $this->missingImg[] = ['value' => $index];
        }
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
