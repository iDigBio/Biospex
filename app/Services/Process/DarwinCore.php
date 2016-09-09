<?php 

namespace App\Services\Process;

ini_set("auto_detect_line_endings", "1");
ini_set("memory_limit", "7G");
ini_set('max_execution_time', '0');
ini_set('max_input_time', '0');
set_time_limit(0);
ignore_user_abort(true);

use App\Services\Csv\DarwinCoreCsvImport;
use Illuminate\Contracts\Filesystem\FileNotFoundException;


class DarwinCore
{

    /**
     * @var MetaFile
     */
    public $metaFile;

    /**
     * DarwinCoreCsv Class
     * @var DarwinCoreCsvImport
     */
    public $csv;

    /**
     * Id of project
     * @var integer
     */
    public $projectId;

    /**
     * Sets if media is core or extension in meta file
     * @var bool
     */
    public $mediaIsCore;

    /**
     * Array for meta file fields: core and extension
     * @var array
     */
    public $metaFields;

    /**
     * Construct
     * @param MetaFile $metaFile
     * @param DarwinCoreCsvImport $csv
     */
    public function __construct(
        MetaFile $metaFile,
        DarwinCoreCsvImport $csv
    )
    {
        $this->metaFile = $metaFile;
        $this->csv = $csv;
    }

    /**
     * Process Darwin Core Import.
     * 
     * @param $projectId
     * @param $directory
     * @throws FileNotFoundException
     */
    public function process($projectId, $directory)
    {
        $this->projectId = $projectId;

        // Parse meta file, set properties, and save to database.
        $meta = $this->processMetaFile($directory);

        // Load media first to create subjects
        $this->processCsvFile($directory);

        // Load occurrences
        $this->processCsvFile($directory, false);

        $this->metaFile->saveMetaFile($projectId, $meta);

    }

    /**
     * Check file exists.
     *
     * @param $file
     * @throws FileNotFoundException
     */
    protected function checkFileExists($file)
    {
        if ( ! file_exists($file))
        {
            throw new FileNotFoundException(trans('emails.error_import_file_does_not_exist', ['file' => $file]));
        }
    }

    /**
     * Process meta file, set properties, and save to database
     *
     * @param $directory
     * @return string
     */
    public function processMetaFile($directory)
    {
        $this->checkFileExists($directory . '/meta.xml');

        $meta = $this->metaFile->process($directory . '/meta.xml');
        $this->mediaIsCore = $this->metaFile->getMediaIsCore();
        $this->metaFields = $this->metaFile->getMetaFields();

        // Set meta properties needed in handling csv file.
        $this->csv->setCsvMetaProperties($this->mediaIsCore, $this->metaFields, $this->projectId);

        return $meta;
    }

    /**
     * Process a darwin core csv file
     * @param $directory
     * @param bool $loadMedia
     * @return array
     * @throws FileNotFoundException
     */
    protected function processCsvFile($directory, $loadMedia = true)
    {
        $type = $this->setFileType($loadMedia);
        $file = $this->setFilePath($directory, $type);

        $this->checkFileExists($file);

        $delimiter = $this->setDelimiter($type);
        $enclosure = $this->setEnclosure($type);
        $this->csv->loadCsvFile($file, $delimiter, $enclosure, $type, $loadMedia);
    }

    /**
     * Set file type being worked on from meta file
     * @param $loadMedia
     * @return string
     */
    public function setFileType($loadMedia)
    {
        if ($loadMedia === true)
        {
            return $this->mediaIsCore ? 'core' : 'extension';
        }
        else
        {
            return $this->mediaIsCore ? 'extension' : 'core';
        }
    }

    /**
     * Set File to work with
     * @param $directory
     * @param $type
     * @return string
     */
    public function setFilePath($directory, $type)
    {
        if ($type === 'core') {
            $file = $directory . '/' . $this->metaFile->getCoreFile();
        } else {
            $file = $directory . '/' . $this->metaFile->getExtensionFile();
        }

        return $file;
    }

    /**
     * Set delimiter
     * @param $type
     * @return mixed
     */
    public function setDelimiter($type)
    {
        return ($type === 'core') ? $this->metaFile->getCoreDelimiter() : $this->metaFile->getExtDelimiter();
    }

    /**
     * Set enclosure
     * @param $type
     * @return mixed
     */
    public function setEnclosure($type)
    {
        return ($type === 'core') ? $this->metaFile->getCoreEnclosure() : $this->metaFile->getExtEnclosure();
    }

    /**
     * Get duplicate records
     * @return array
     */
    public function getDuplicates()
    {
        return $this->csv->getDuplicates();
    }

    /**
     * Get rejected media
     * @return array
     */
    public function getRejectedMedia()
    {
        return $this->csv->getRejectedMedia();
    }

    /**
     * @return int
     */
    public function getSubjectCount()
    {
        return $this->csv->subjectCount;
    }
}