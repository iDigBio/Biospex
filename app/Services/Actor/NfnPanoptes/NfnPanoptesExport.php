<?php

namespace App\Services\Actor\NfnPanoptes;

ini_set('memory_limit', '1024M');

use App\Services\Actor\ActorAbstract;
use App\Services\Actor\ActorInterface;
use Illuminate\Config\Repository as Config;
use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\Expedition;
use App\Services\Report\Report;
use App\Services\Image\Image;
use Illuminate\Filesystem\Filesystem;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use App\Services\Csv\Csv;

class NfnPanoptesExport extends ActorAbstract implements ActorInterface
{
    /**
     * Current expedition being processed.
     *
     * @var object
     */
    public $record;

    /** Notes from Nature export directory */
    public $nfnExportDir;

    /**
     * Full path to temp directory named after expedition title with md5 hash.
     *
     * @var string
     */
    public $recordDir;

    /**
     * Full path to temp directory inside $recordDir for image conversions.
     *
     * @var
     */
    public $recordDirTmp;

    /**
     * Missing image when retrieving via curl.
     *
     * @var array
     */
    public $missingImg = [];

    /**
     * @var Expedition
     */
    public $expedition;

    /**
     * @var Report
     */
    public $report;

    /**
     * @var Image
     */
    public $image;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var mixed
     */
    public $nfnCsvMap;

    /**
     * @var array
     */
    public $csvExport = [];

    /**
     * @var Csv
     */
    public $csv;

    /**
     * NotesFromNatureOrigExport constructor.
     * @param Filesystem $filesystem
     * @param Download $download
     * @param Config $config
     * @param Expedition $expedition
     * @param Report $report
     * @param Image $image
     * @param Csv $csv
     */
    public function __construct(
        Filesystem $filesystem,
        Download $download,
        Config $config,
        Expedition $expedition,
        Report $report,
        Image $image,
        Csv $csv
    )
    {
        $this->filesystem = $filesystem;
        $this->download = $download;
        $this->config = $config;
        $this->expedition = $expedition;
        $this->report = $report;
        $this->image = $image;
        $this->csv = $csv;
        $this->client = new Client();

        $this->scratchDir = $config->get('config.scratch_dir');
        $this->nfnExportDir = $config->get('config.nfn_export_dir');
        $this->nfnCsvMap = $config->get('config.nfnCsvMap');
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

        $this->expedition->cached(false);
        
        $this->record = $this->expedition->findWith($actor->pivot->expedition_id, ['project.group', 'subjects']);

        if ($this->record === null)
        {
            throw new \Exception(trans('emails.error_process', ['id' => $actor->pivot->expedition_id]));
        }

        $this->setRecordDir($actor->id. '-' . md5($this->record->title));

        $this->buildCsvArray($actor->pivot->expedition_id);

        $this->getImagesFromUri();

        $this->convert();
        
        $this->createCsv();
        
        $tarGzFile = $this->compressDir();
        
        $this->createDownloads($actor, $tarGzFile);

        $this->moveCompressedFiles($tarGzFile);

        $this->filesystem->deleteDirectory($this->recordDirTmp);
        $this->filesystem->deleteDirectory($this->recordDir);
        
        $this->processComplete();
        
        $actor->pivot->queued = 0;
        ++$actor->pivot->state;
        $actor->pivot->completed = 1;
        $actor->pivot->save();
    }

    /**
     * Build csvExport array for export.
     * @param $expeditionId
     */
    public function buildCsvArray($expeditionId)
    {
        foreach ($this->record->subjects as $subject)
        {
            $this->csvExport[] = $this->mapNfnCsvColumns($subject, $expeditionId);
        }
    }

    /**
     * Map nfn csvExport values from configuration
     * @param $subject
     * @param $expeditionId
     * @return array
     */
    public function mapNfnCsvColumns($subject, $expeditionId)
    {
        $csvArray = [];
        foreach ($this->nfnCsvMap as $key => $item)
        {
            if ($key === '#expeditionId')
            {
                $csvArray[$key] = $expeditionId;
                continue;
            }
            if ( ! is_array($item))
            {
                $csvArray[$key] = $item === '' ? '' : $subject->{$item};

                continue;
            }

            $csvArray[$key] = '';
            foreach ($item as $doc => $value)
            {
                if (isset($subject->{$doc}->{$value}))
                {
                    $csvArray[$key] = $subject->{$doc}->{$value};
                    break;
                }
            }
        }

        return $csvArray;
    }

    /**
     * Process expedition for export
     */
    public function getImagesFromUri()
    {
        $requests = function ($csvExport)
        {
            foreach ($csvExport as $index => $row)
            {

                if ($this->checkUriExists($row) || $this->checkImageDoesNotExists($row['subjectId']))
                {
                    yield $index => new Request('GET', str_replace(' ', '%20', $row['imageURL']));
                }
            }
        };

        $pool = new Pool($this->client, $requests($this->csvExport), [
            'concurrency' => 10,
            'fulfilled'   => function ($response, $index)
            {
                $code = $response->getStatusCode();
                $image = $response->getBody();
                $this->saveImage($code, $index, $image);
            },
            'rejected'    => function ($reason, $index)
            {
                $this->addMissingImage($this->csvExport[$index]['subjectId'], $this->csvExport[$index]['imageURL']);
            }
        ]);

        $promise = $pool->promise();

        $promise->wait();
    }

    /**
     * Check if image exists
     *
     * @param $id
     * @return bool
     */
    public function checkImageDoesNotExists($id)
    {
        return count($this->filesystem->glob($this->recordDir . '/' . $id . '.*')) === 0;
    }

    /**
     * Check if image exists
     * @param $row
     * @return bool
     */
    public function checkUriExists($row)
    {
        if (empty($row['imageURL'])) {
            $this->addMissingImage($row['subjectId']);
            
            return false;
        }

        return true;
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
        if ($image ==='' || $code !== 200)
        {
            $this->addMissingImage($this->csvExport[$index]['subjectId'], $this->csvExport[$index]['imageURL']);

            return;
        }

        $this->image->setImageSizeInfoFromString($image);
        $ext = $this->image->getFileExtension();

        if ( ! $ext)
        {
            $this->addMissingImage($this->csvExport[$index]['subjectId'], $this->csvExport[$index]['imageURL']);

            return;
        }

        $fileName = $this->csvExport[$index]['subjectId'] . '.' . $ext;
        $this->csvExport[$index]['imageName'] = $fileName;
        $path = $this->recordDir . '/' . $fileName;

        $this->saveFile($path, $image);
    }

    /**
     * Convert images.
     */
    public function convert()
    {
        $files = $this->filesystem->files($this->recordDir);

        if (count($files) === 0)
        {
            return;
        }

        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);

            if ($this->image->getMimeType() === false)
            {
                continue;
            }

            $fileName = $this->image->getFileName();
            $ext = $this->image->getFileExtension();

            try
            {
                $this->image->imagickFile($file);
            }
            catch (\Exception $e)
            {
                $key = array_search($fileName, array_column($this->csvExport, 'subjectId'), true);
                $this->addMissingImage($fileName, $this->csvExport[$key]['imageURL']);

                continue;
            }

            $imgFilePath = $this->recordDirTmp . '/' . $fileName . '.' . $ext;

            if (!$this->filesystem->exists($imgFilePath))
            {
                $this->image->imagickScale($imgFilePath, $this->config->get('config.nfnImageSize.largeWidth'), 0);
            }

            $this->image->imagickDestroy();
        }
    }

    /**
     * Create csv file
     */
    public function createCsv()
    {
        $this->csv->writerCreateFromPath($this->recordDirTmp . '/' . $this->record->uuid . '.csv');
        $this->csv->insertOne(array_keys($this->csvExport[0]));
        $this->csv->insertAll($this->csvExport);
    }

    /**
     * @param $folder
     * @throws \Exception
     */
    public function setRecordDir($folder)
    {
        $this->recordDir = $this->scratchDir . '/' . $folder;
        $this->recordDirTmp = $this->recordDir . '/' . $this->record->uuid;
        $this->createDir($this->recordDirTmp);
        $this->writeDir($this->recordDirTmp);
    }


    /**
     * Add missing image information to array
     * @param null $index
     * @param null $uri
     */
    public function addMissingImage($index = null, $uri = null)
    {
        if (!is_null($index) && !is_null($uri))
        {
            $this->missingImg[] = ['value' => $index . ' : ' . $uri];
        }

        if (is_null($index) && !is_null($uri))
        {
            $this->missingImg[] = ['value' => $uri];
        }

        if (!is_null($index) && is_null($uri))
        {
            $this->missingImg[] = ['value' => $index];
        }
    }

    /**
     * Compress directory.
     */
    public function compressDir()
    {
        $tarFile = $this->recordDirTmp . '.tar';
        $a = new \PharData($tarFile);
        $a->buildFromDirectory($this->recordDirTmp);
        $a->compress(\Phar::GZ);
        unset($a);
        $this->filesystem->delete($tarFile);
        $this->filesystem->deleteDirectory($this->recordDirTmp);
        
        return $tarFile . '.gz';
    }

    /**
     * Create download
     * @param $actor
     * @param $file
     */
    public function createDownloads($actor, $file)
    {
        $baseName = pathinfo($file, PATHINFO_BASENAME);
        $this->createDownload($this->record->id, $actor->id, $baseName);
    }

    /**
     * Move tar.gz file to export folder.
     * @param $file
     * @throws \Exception
     */
    public function moveCompressedFiles($file)
    {
        $baseName = pathinfo($file, PATHINFO_BASENAME);
        $this->moveFile($file, "{$this->nfnExportDir}/$baseName");
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
