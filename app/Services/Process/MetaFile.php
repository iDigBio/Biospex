<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Process;

use App\Repositories\MetaRepository;
use Exception;

/**
 * Class MetaFile
 */
class MetaFile
{
    /**
     * @var Xml
     */
    protected $xml;

    /**
     * @var null
     */
    protected $core;

    /**
     * @var null
     */
    protected $extension;

    /**
     * @var array
     */
    protected $dwcRequiredRowTypes;

    protected $mediaIsCore;

    protected $coreFile;

    protected $extensionFile;

    protected $coreDelimiter;

    protected $coreEnclosure;

    protected $extDelimiter;

    protected $extEnclosure;

    protected $metaFields;

    /**
     * @var \App\Repositories\MetaRepository
     */
    protected $metaRepo;

    protected $file;

    /**
     * @var array
     */
    protected $dwcRequiredFields;

    /**
     * Constructor
     */
    public function __construct(MetaRepository $metaRepo, Xml $xml)
    {
        $this->xml = $xml;
        $this->metaRepo = $metaRepo;

        $this->dwcRequiredRowTypes = config('config.dwcRequiredRowTypes');
        $this->dwcRequiredFields = config('config.dwcRequiredFields');
    }

    /**
     * Process meta file.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function process($file)
    {
        $this->file = $file;
        $xml = $this->xml->load($file);
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
     */
    public function saveMetaFile($projectId, $meta)
    {
        $this->metaRepo->create([
            'project_id' => $projectId,
            'xml' => $meta,
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
     * @throws \Exception
     */
    public function loadExtensionNode()
    {
        $extensions = $this->xml->xpathQuery('//ns:archive/ns:extension');

        if ($this->loopExtensions($extensions)) {
            return true;
        }

        throw new Exception(t('Unable to determine meta file extension during Darwin Core Archive import. This is typically due to missing required DWC row types. File: %s', $this->file));
    }

    /**
     * Loop through extensions found using xpath query.
     *
     * @param  array  $extensions
     * @return bool
     */
    protected function loopExtensions($extensions)
    {
        foreach ($extensions as $extension) {
            $matches = $this->loopExtension($extension);

            if ($matches >= count($this->dwcRequiredFields['extension'])) {
                $this->extension = $extension;

                return true;
            }
        }

        return false;
    }

    /**
     * Loop through extension.
     *
     * @return int
     */
    protected function loopExtension($extension)
    {
        $matches = 0;
        foreach ($this->dwcRequiredFields['extension'] as $field => $terms) {
            if (count($terms) === 0 && count($extension->getElementsByTagName($field)) > 0) {
                $matches++;

                continue;
            }

            $this->checkExtensionTerms($extension, $terms, $matches);
        }

        return $matches;
    }

    /**
     * Check terms in extension node.
     */
    protected function checkExtensionTerms($extension, $terms, &$matches)
    {
        foreach ($terms as $value) {
            if ((int) $this->xml->evaluate('count(ns:field[@term=\''.$value.'\'])', $extension)) {
                $matches++;

                break;
            }
        }
    }

    /**
     * Check row type against file given and send warning if mismatch occurs
     *
     * @throws \Exception
     */
    private function checkExtensionRowType()
    {
        $rowType = strtolower($this->extension->attributes->getNamedItem('rowType')->nodeValue);
        if (in_array($rowType, $this->dwcRequiredRowTypes, true)) {
            return;
        }

        throw new Exception(t('Row Type mismatch in reading meta xml file. :file, :row_type, :type_file',
            [':file' => $this->file, ':row_type' => $rowType, ':type_file' => $this->extension->nodeValue]
        ));
    }

    /**
     * Set if multimedia is the core.
     */
    private function setMediaIsCore()
    {
        $rowType = $this->core->attributes->getNamedItem('rowType')->nodeValue;
        $this->mediaIsCore = stripos($rowType, 'occurrence') === false;

    }

    /**
     * Set core file.
     *
     * @throws \Exception
     */
    private function setCoreFile()
    {
        $this->coreFile = $this->core->nodeValue;
        if ($this->coreFile === '') {
            throw new Exception(t('Core node missing from xml meta file.'));
        }
    }

    /**
     * Set extension file.
     *
     * @throws \Exception
     */
    private function setExtensionFile()
    {
        $this->extensionFile = $this->extension->nodeValue;
        if ($this->extensionFile === '') {
            throw new Exception(t('Extension node missing from xml meta file'));
        }
    }

    /**
     * Set csv settings for core file.
     *
     * @throws \Exception
     */
    private function setCoreCsvSettings()
    {
        $delimiter = $this->core->attributes->getNamedItem('fieldsTerminatedBy')->nodeValue;
        $this->coreDelimiter = ($delimiter === '\\t') ? "\t" : $delimiter;
        $enclosure = $this->core->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;
        $this->coreEnclosure = $enclosure === '' ? '"' : $enclosure;

        if ($this->coreDelimiter === '') {
            throw new Exception(t('CSV core delimiter is empty.'));
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
        $this->extDelimiter = ($delimiter === '\\t') ? "\t" : $delimiter;
        $enclosure = $this->extension->attributes->getNamedItem('fieldsEnclosedBy')->nodeValue;
        $this->extEnclosure = $enclosure === '' ? '"' : $enclosure;

        if ($this->extDelimiter === '') {
            throw new Exception(t('CSV extension delimiter is empty.'));
        }
    }

    /**
     * Set meta fields.
     */
    private function setMetaFields($type)
    {
        foreach ($this->{$type}->childNodes as $child) {
            if ($child->tagName === 'files') {
                continue;
            }

            $index = $child->attributes->getNamedItem('index')->nodeValue;

            if ($child->tagName === 'id' || $child->tagName === 'coreid') {
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
