<?php 

namespace App\Services\Process;

use App\Repositories\Contracts\Meta;
use App\Services\Report\Report;
use Illuminate\Support\Facades\Config;

class MetaFile
{

    protected $xml;
    protected $report;
    protected $core = null;
    protected $extension = null;
    protected $metaFileRowTypes;
    protected $mediaIsCore;
    protected $coreFile;
    protected $extensionFile;
    protected $coreDelimiter;
    protected $coreEnclosure;
    protected $extDelimiter;
    protected $extEnclosure;
    protected $metaFields;
    protected $meta;
    protected $file;

    /**
     * Constructor
     *
     * @param Meta $meta
     * @param Xml $xml
     * @param Report $report
     */
    public function __construct(Meta $meta, Xml $xml, Report $report)
    {
        $this->xml = $xml;
        $this->report = $report;
        $this->metaFileRowTypes = Config::get('config.metaFileRowTypes');
        $this->meta = $meta;
    }

    /**
     * Process meta file.
     *
     * @param $file
     * @return string
     * @throws \Exception
     */
    public function process($file)
    {
        $this->file = $file;

        $xml = $this->xml->load($file);

        // New
        $this->loadCoreNode();
        $this->loadExtensionNode();
        $this->checkExtensionRowType();
        $this->setMediaIsCore();
        $this->setCoreFile();
        $this->setExtensionFile();
        $this->setCoreCsvSettings();
        $this->setExtensionCsvSettings();
        $this->setMetaFields('core');
        $this->setMetaFields('extension');

        return $xml;
    }

    /**
     * Save meta data for this upload.
     *
     * @param $projectId
     * @param $meta
     */
    public function saveMetaFile($projectId, $meta)
    {
        $this->meta->create([
            'project_id' => $projectId,
            'xml'        => $meta,
        ]);
    }

    /**
     * Load core node from meta file.
     */
    public function loadCoreNode()
    {
        $query = '//ns:archive/ns:core';
        $this->core = $this->xml->xpathQuery($query, true);
    }

    /**
     * Load extension node from meta file.
     * TODO Loads extension using file location from config. Need more robust method.
     */
    public function loadExtensionNode()
    {
        foreach ($this->metaFileRowTypes as $rowType => $fileNames)
        {
            foreach ($fileNames as $fileName)
            {
                if ($this->findExtensionFile($fileName))
                {
                    return;
                }
            }
        }

        $this->report->addError(trans('emails.error_extension_file', ['file' => $this->file]));
        $this->report->reportSimpleError();

    }

    protected function findExtensionFile($fileName)
    {
        $query = "//ns:archive/ns:extension[contains(ns:files/ns:location, '" . $fileName . ".')]";
        $this->extension = $this->xml->xpathQuery($query, true);

        return empty($this->extension) ? false : true;
    }

    /**
     * Check row type against file given and send warning if mismatch occurs
     */
    private function checkExtensionRowType()
    {
        $rowType = strtolower($this->extension->attributes->getNamedItem('rowType')->nodeValue);
        if (isset($this->metaFileRowTypes[$rowType]))
        {
            return;
        }

        $this->report->addError(trans('emails.error_rowtype_mismatch',
            ['file' => $this->file, 'row_type' => $rowType, 'type_file' => $this->extension->nodeValue]
        ));
        $this->report->reportSimpleError();

    }

    /**
     * Set if multimedia is the core.
     */
    private function setMediaIsCore()
    {
        $rowType = $this->core->attributes->getNamedItem('rowType')->nodeValue;
        $this->mediaIsCore = preg_match('/occurrence/i', $rowType) ? false : true;

    }

    /**
     * Set core file.
     *
     * @throws \Exception
     */
    private function setCoreFile()
    {
        $this->coreFile = $this->core->nodeValue;
        if (empty($this->coreFile))
        {
            throw new \Exception(trans('emails.error_core_file_missing'));
        }
    }

    /**
     * Set extension file.
     */
    private function setExtensionFile()
    {
        $this->extensionFile = $this->extension->nodeValue;
    }

    /**
     * Set csv settings for core file.
     *
     * @throws \Exception
     */
    private function setCoreCsvSettings()
    {
        $delimiter = $this->core->attributes->getNamedItem('fieldsTerminatedBy')->nodeValue;
        $this->coreDelimiter = ($delimiter === "\\t") ? "\t" : $delimiter;
        $this->coreEnclosure = $this->core->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;

        if (empty($this->coreDelimiter))
        {
            throw new \Exception(trans('emails.error_csv_core_delimiter'));
        }
    }

    /**
     * Set csv settings for extension file.
     *
     * @throws \Exception
     */
    private function setExtensionCsvSettings()
    {
        $delimiter = $this->extension->attributes->getNamedItem('fieldsTerminatedBy')->nodeValue;
        $this->extDelimiter = ($delimiter === "\\t") ? "\t" : $delimiter;
        $this->extEnclosure = $this->extension->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;

        if (empty($this->extDelimiter))
        {
            throw new \Exception(trans('emails.error_csv_ext_delimiter'));
        }
    }

    /**
     * Set meta fields.
     *
     * @param $type
     */
    private function setMetaFields($type)
    {
        foreach ($this->$type->childNodes as $child)
        {
            if ($child->tagName === 'files')
            {
                continue;
            }

            $index = $child->attributes->getNamedItem('index')->nodeValue;

            if ($child->tagName === 'id' || $child->tagName === 'coreid')
            {
                $this->metaFields[$type][$index] = $child->tagName;
                continue;
            }

            $qualified = $child->attributes->getNamedItem('term')->nodeValue;

            $this->metaFields[$type][$index] = $qualified;
        }

    }

    /**
     * @return mixed
     */
    public function getCoreFile()
    {
        return $this->coreFile;
    }

    /**
     * @return mixed
     */
    public function getCoreDelimiter()
    {
        return $this->coreDelimiter;
    }

    /**
     * @return mixed
     */
    public function getCoreEnclosure()
    {
        return $this->coreEnclosure;
    }

    /**
     * @return mixed
     */
    public function getExtensionFile()
    {
        return $this->extensionFile;
    }

    /**
     * @return mixed
     */
    public function getExtDelimiter()
    {
        return $this->extDelimiter;
    }

    /**
     * @return mixed
     */
    public function getExtEnclosure()
    {
        return $this->extEnclosure;
    }

    /**
     * @return mixed
     */
    public function getMediaIsCore()
    {
        return $this->mediaIsCore;
    }

    /**
     * @return mixed
     */
    public function getMetaFields()
    {
        return $this->metaFields;
    }
}
