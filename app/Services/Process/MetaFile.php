<?php

namespace App\Services\Process;

use App\Exceptions\BiospexException;
use App\Exceptions\ExtensionMissingException;
use App\Exceptions\MissingCsvDelimiter;
use App\Exceptions\MissingNodeException;
use App\Exceptions\RowTypeMismatchException;
use App\Exceptions\XmlLoadException;
use RuntimeException;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\Meta;
use App\Services\Report\Report;


class MetaFile
{

    /**
     * @var Xml
     */
    protected $xml;

    /**
     * @var Report
     */
    protected $report;

    /**
     * @var null
     */
    protected $core;

    /**
     * @var null
     */
    protected $extension;

    /**
     * @var array $dwcRequiredRowTypes
     */
    protected $dwcRequiredRowTypes;

    /**
     * @var
     */
    protected $mediaIsCore;

    /**
     * @var
     */
    protected $coreFile;

    /**
     * @var
     */
    protected $extensionFile;

    /**
     * @var
     */
    protected $coreDelimiter;

    /**
     * @var
     */
    protected $coreEnclosure;

    /**
     * @var
     */
    protected $extDelimiter;

    /**
     * @var
     */
    protected $extEnclosure;

    /**
     * @var
     */
    protected $metaFields;

    /**
     * @var Meta
     */
    protected $meta;

    /**
     * @var
     */
    protected $file;

    /**
     * @var array $dwcRequiredFields
     */
    protected $dwcRequiredFields;

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
        $this->meta = $meta;

        $this->dwcRequiredRowTypes = Config::get('config.dwcRequiredRowTypes');
        $this->dwcRequiredFields = Config::get('config.dwcRequiredFields');
    }

    /**
     * Process meta file.
     *
     * @param $file
     * @return string
     * @throws BiospexException
     */
    public function process($file)
    {
        $this->file = $file;

        try
        {
            $xml = $this->xml->load($file);
        }
        catch (RuntimeException $e)
        {
            throw new XmlLoadException($e->getMessage());
        }

        $this->loadCoreNode();
        $this->setMediaIsCore();
        $this->loadExtensionNode();
        $this->checkExtensionRowType();
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
     *
     * @throws ExtensionMissingException
     */
    public function loadExtensionNode()
    {
        $extensions = $this->xml->xpathQuery('//ns:archive/ns:extension');

        if ($this->loopExtensions($extensions))
        {
            return;
        }

        throw new ExtensionMissingException(trans('errors.missing_meta_extension', ['file' => $this->file]));
    }

    /**
     * Loop through extensions found using xpath query.
     *
     * @param array $extensions
     * @return bool
     */
    protected function loopExtensions($extensions)
    {
        foreach ($extensions as $extension)
        {
            $matches = $this->loopExtension($extension);

            if ($matches >= count($this->dwcRequiredFields['extension']))
            {
                $this->extension = $extension;

                return true;
            }
        }

        return false;
    }

    /**
     * Loop through extension.
     *
     * @param $extension
     * @return int
     */
    protected function loopExtension($extension)
    {
        $matches = 0;
        foreach ($this->dwcRequiredFields['extension'] as $field => $terms)
        {
            if (count($terms) === 0 && count($extension->getElementsByTagName($field)) > 0)
            {
                $matches++;

                continue;
            }

            $this->checkExtensionTerms($extension, $terms, $matches);
        }

        return $matches;
    }

    /**
     * Check terms in extension node.
     *
     * @param $extension
     * @param $terms
     * @param $matches
     */
    protected function checkExtensionTerms($extension, $terms, &$matches)
    {
        foreach ($terms as $value)
        {
            if ((int) $this->xml->evaluate('count(ns:field[@term=\'' . $value . '\'])', $extension))
            {
                $matches++;

                break;
            }
        }
    }

    /**
     * Check row type against file given and send warning if mismatch occurs
     * @throws RowTypeMismatchException
     */
    private function checkExtensionRowType()
    {
        $rowType = strtolower($this->extension->attributes->getNamedItem('rowType')->nodeValue);
        if (in_array($rowType, $this->dwcRequiredRowTypes, true))
        {
            return;
        }

        throw new RowTypeMismatchException(trans('errors.rowtype_mismatch',
            ['file' => $this->file, 'row_type' => $rowType, 'type_file' => $this->extension->nodeValue]
        ));
    }

    /**
     * Set if multimedia is the core.
     */
    private function setMediaIsCore()
    {
        $rowType = $this->core->attributes->getNamedItem('rowType')->nodeValue;
        $this->mediaIsCore = false === stripos($rowType, 'occurrence');

    }

    /**
     * Set core file.
     *
     * @throws MissingNodeException
     */
    private function setCoreFile()
    {
        $this->coreFile = $this->core->nodeValue;
        if ($this->coreFile === '')
        {
            throw new MissingNodeException(trans('errors.core_node_missing'));
        }
    }

    /**
     * Set extension file.
     * @throws MissingNodeException
     */
    private function setExtensionFile()
    {
        $this->extensionFile = $this->extension->nodeValue;
        if ($this->extensionFile === '')
        {
            throw new MissingNodeException(trans('errors.extension_node_missing'));
        }
    }

    /**
     * Set csv settings for core file.
     *
     * @throws MissingCsvDelimiter
     */
    private function setCoreCsvSettings()
    {
        $delimiter = $this->core->attributes->getNamedItem('fieldsTerminatedBy')->nodeValue;
        $this->coreDelimiter = ($delimiter === "\\t") ? "\t" : $delimiter;
        $enclosure = $this->core->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;
        $this->coreEnclosure = $enclosure === '' ? '"' : $enclosure;

        if ($this->coreDelimiter === '')
        {
            throw new MissingCsvDelimiter(trans('errors.csv_core_delimiter'));
        }
    }

    /**
     * Set csv settings for extension file.
     *
     * @throws MissingCsvDelimiter
     */
    private function setExtensionCsvSettings()
    {
        $delimiter = $this->extension->attributes->getNamedItem('fieldsTerminatedBy')->nodeValue;
        $this->extDelimiter = ($delimiter === "\\t") ? "\t" : $delimiter;
        $enclosure = $this->extension->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;
        $this->extEnclosure = $enclosure === '' ? '"' : $enclosure;

        if ($this->extDelimiter === '')
        {
            throw new MissingCsvDelimiter(trans('errors.csv_ext_delimiter'));
        }
    }

    /**
     * Set meta fields.
     *
     * @param $type
     */
    private function setMetaFields($type)
    {
        foreach ($this->{$type}->childNodes as $child)
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
