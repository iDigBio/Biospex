<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Process;

use App\Models\Meta;
use Exception;

/**
 * Class MetaFile
 * Handles the processing and management of Darwin Core Archive meta.xml files.
 * This class is responsible for parsing meta.xml files, extracting core and extension
 * information, validating required fields, and managing CSV settings for data import.
 */
class MetaFile
{
    /**
     * @var \DOMElement|null XML core element from meta.xml
     */
    protected ?\DOMElement $core;

    /**
     * @var \DOMElement|null XML extension element from meta.xml
     */
    protected ?\DOMElement $extension;

    /**
     * @var array Required row types for Darwin Core Archive
     */
    protected mixed $dwcRequiredRowTypes;

    /**
     * @var bool Indicates if media is the core element
     */
    protected bool $mediaIsCore;

    /**
     * @var string Path to the core data file
     */
    protected string $coreFile;

    /**
     * @var string Path to the extension data file
     */
    protected string $extensionFile;

    /**
     * @var string Delimiter used in the core CSV file
     */
    protected string $coreDelimiter;

    /**
     * @var string Enclosure character used in the core CSV file
     */
    protected string $coreEnclosure;

    /**
     * @var string Delimiter used in the extension CSV file
     */
    protected string $extDelimiter;

    /**
     * @var string Enclosure character used in the extension CSV file
     */
    protected string $extEnclosure;

    /**
     * @var array Mapped fields from meta.xml for both core and extension
     */
    protected array $metaFields;

    /**
     * @var string Current meta.xml file being processed
     */
    protected string $file;

    /**
     * @var array Required fields for Darwin Core Archive
     */
    protected array $dwcRequiredFields;

    /**
     * Constructor for MetaFile
     *
     * @param  Meta  $meta  Meta model instance for database operations
     * @param  Xml  $xml  XML processing service instance
     */
    public function __construct(protected Meta $meta, protected Xml $xml)
    {
        $this->dwcRequiredRowTypes = config('config.dwcRequiredRowTypes');
        $this->dwcRequiredFields = config('config.dwcRequiredFields');
    }

    /**
     * Process the meta.xml file from Darwin Core Archive
     * Loads and validates XML structure, processes core and extension nodes,
     * and sets up CSV configuration for data import.
     *
     * @param  string  $file  Path to the meta.xml file
     * @return string Processed XML content
     *
     * @throws \Exception When required elements are missing or invalid
     */
    public function process(string $file): string
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
     * Save meta-data for the current upload to a database
     *
     * @param  int  $projectId  Project identifier
     * @param  string  $meta  XML content to save
     */
    public function saveMetaFile(int $projectId, string $meta): void
    {
        $this->meta->create([
            'project_id' => $projectId,
            'xml' => $meta,
        ]);
    }

    /**
     * Load core node from meta file.
     *
     * @throws \Exception
     */
    public function loadCoreNode(): void
    {
        $query = '//ns:archive/ns:core';
        $this->core = $this->xml->xpathQuery($query, true);

        if ($this->core === null) {
            throw new Exception(t('Core element missing from meta.xml file. Darwin Core Archive must contain a <core> element. File: :file', [':file' => $this->file]));
        }
    }

    /**
     * Load extension node from meta file.
     *
     * @throws \Exception
     */
    public function loadExtensionNode(): bool
    {
        $extensions = $this->xml->xpathQuery('//ns:archive/ns:extension');

        if ($this->loopExtensions($extensions)) {
            return true;
        }

        throw new Exception(t('Unable to determine meta file extension during Darwin Core Archive import. This is typically due to missing required DWC row types. File: %s', $this->file));
    }

    /**
     * Loop through extensions found using a xpath query.
     */
    protected function loopExtensions(array $extensions): bool
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
     */
    protected function loopExtension(\DOMElement $extension): int
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
    protected function checkExtensionTerms(\DOMElement $extension, array $terms, int &$matches): void
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
        if ($this->extension === null || $this->extension->attributes === null) {
            throw new Exception(t('Extension element or its attributes are missing from meta.xml file. File: :file', [':file' => $this->file]));
        }

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
     *
     * @throws \Exception
     */
    private function setMediaIsCore(): void
    {
        if ($this->core === null || $this->core->attributes === null) {
            throw new Exception(t('Core element or its attributes are missing from meta.xml file. File: :file', [':file' => $this->file]));
        }

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
        if ($this->core === null) {
            throw new Exception(t('Core element missing from meta.xml file. File: :file', [':file' => $this->file]));
        }

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
        if ($this->core === null || $this->core->attributes === null) {
            throw new Exception(t('Core element or its attributes are missing from meta.xml file. File: :file', [':file' => $this->file]));
        }

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
        if ($this->extension === null || $this->extension->attributes === null) {
            throw new Exception(t('Extension element or its attributes are missing from meta.xml file. File: :file', [':file' => $this->file]));
        }

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
     *
     * @throws \Exception
     */
    private function setMetaFields(string $type): void
    {
        if ($this->{$type} === null) {
            throw new Exception(t(':type element missing from meta.xml file. File: :file', [':type' => ucfirst($type), ':file' => $this->file]));
        }

        foreach ($this->{$type}->childNodes as $child) {
            if ($child->tagName === 'files') {
                continue;
            }

            if ($child->attributes === null) {
                continue; // Skip nodes without attributes
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
    public function getCoreFile(): string
    {
        return $this->coreFile;
    }

    /**
     * @return mixed
     */
    public function getCoreDelimiter(): string
    {
        return $this->coreDelimiter;
    }

    /**
     * @return mixed
     */
    public function getCoreEnclosure(): string
    {
        return $this->coreEnclosure;
    }

    /**
     * @return mixed
     */
    public function getExtensionFile(): string
    {
        return $this->extensionFile;
    }

    /**
     * @return mixed
     */
    public function getExtDelimiter(): string
    {
        return $this->extDelimiter;
    }

    /**
     * @return mixed
     */
    public function getExtEnclosure(): string
    {
        return $this->extEnclosure;
    }

    /**
     * @return mixed
     */
    public function getMediaIsCore(): bool
    {
        return $this->mediaIsCore;
    }

    /**
     * @return mixed
     */
    public function getMetaFields(): array
    {
        return $this->metaFields;
    }
}
