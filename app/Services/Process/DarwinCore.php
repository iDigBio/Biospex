<?php namespace App\Services\Process;

ini_set("auto_detect_line_endings", "1");
ini_set("memory_limit", "7G");
ini_set('max_execution_time', '0');
ini_set('max_input_time', '0');
set_time_limit(0);
ignore_user_abort(true);

use App\Services\Csv\DarwinCoreCsvImport;

class DarwinCore
{

    /**
     * @var MetaFile
     */
    public $metaFile;

    /**
     * DarwinCoreCsv Class
     * @var DarwinCoreCsv
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
     * Process Darwin Core Import
     *
     * @param $projectId
     * @param $directory
     * @param $processOcr
     */
    public function process($projectId, $directory)
    {
        $this->projectId = $projectId;

        // Parse meta file, set properties, and save to database.
        $this->processMetaFile($directory);

        // Load media first to create subjects
        $this->processCsvFile($directory);

        //$this->setHeaderArray();

        // Load occurrences
        $this->processCsvFile($directory, false);

        return;

    }

    /**
     * Process meta file, set properties, and save to database
     * @param $directory
     */
    public function processMetaFile($directory)
    {
        $meta = $this->metaFile->process($directory . '/meta.xml');
        $this->mediaIsCore = $this->metaFile->getMediaIsCore();
        $this->metaFields = $this->metaFile->getMetaFields();
        $this->metaFile->saveMetaFile($this->projectId, $meta);

        // Set meta properties needed in handling csv file.
        $this->csv->setCsvMetaProperties($this->mediaIsCore, $this->metaFields, $this->projectId);
    }

    /**
     * Process a darwin core csv file
     * @param $directory
     * @param bool $loadMedia
     * @return array
     */
    protected function processCsvFile($directory, $loadMedia = true)
    {
        $type = $this->setFileType($loadMedia);
        $file = $this->setFilePath($directory, $type);
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
        return ($loadMedia == true) ? ($this->mediaIsCore ? 'core' : 'extension') : ($this->mediaIsCore ? 'extension' : 'core');
    }

    /**
     * Set File to work with
     * @param $directory
     * @param $type
     * @return string
     */
    public function setFilePath($directory, $type)
    {
        if ($type == 'core') {
            $file = $directory . "/" . $this->metaFile->getCoreFile();
        } else {
            $file = $directory . "/" . $this->metaFile->getExtensionFile();
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
        return ($type == 'core') ? $this->metaFile->getCoreDelimiter() : $this->metaFile->getExtDelimiter();
    }

    /**
     * Set enclosure
     * @param $type
     * @return mixed
     */
    public function setEnclosure($type)
    {
        return ($type == 'core') ? $this->metaFile->getCoreEnclosure() : $this->metaFile->getExtEnclosure();
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
}