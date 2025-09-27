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

namespace App\Services\DarwinCore;

use App\Services\DarwinCore\Exceptions\InvalidDarwinCoreArchiveException;
use DOMDocument;
use DOMXPath;

/**
 * Darwin Core XML Loader
 *
 * Specialized XML loader for Darwin Core Archive meta.xml files.
 * Provides focused loading and validation specifically for DwC-A structure.
 */
class DarwinCoreXmlLoader
{
    private DOMDocument $document;

    private DOMXPath $xpath;

    public function __construct(
        private readonly string $encoding = 'UTF-8',
        private readonly string $version = '1.0'
    ) {}

    /**
     * Load and validate Darwin Core meta.xml file
     *
     * @param  string  $filePath  Path to the meta.xml file
     * @return DOMDocument Loaded and validated DOM document
     *
     * @throws \App\Services\DarwinCore\Exceptions\InvalidDarwinCoreArchiveException When file cannot be loaded or is invalid
     */
    public function loadMetaFile(string $filePath): DOMDocument
    {
        if (! file_exists($filePath)) {
            throw InvalidDarwinCoreArchiveException::fileNotFound($filePath);
        }

        $this->document = new DOMDocument($this->version, $this->encoding);
        $this->document->preserveWhiteSpace = false;

        // Suppress libxml errors to handle them gracefully
        $useInternalErrors = libxml_use_internal_errors(true);
        $parsed = $this->document->load($filePath);
        $errors = libxml_get_errors();
        libxml_use_internal_errors($useInternalErrors);

        if (! $parsed) {
            $errorMessage = ! empty($errors) ? $errors[0]->message : 'Unknown XML parsing error';
            throw InvalidDarwinCoreArchiveException::xmlParsingFailed($filePath, trim($errorMessage));
        }

        $this->initializeXPath();
        $this->validateXmlStructure();

        return $this->document;
    }

    /**
     * Get the DOM document
     */
    public function getDocument(): DOMDocument
    {
        return $this->document;
    }

    /**
     * Get the XPath processor
     */
    public function getXPath(): DOMXPath
    {
        return $this->xpath;
    }

    /**
     * Get XML content as string
     */
    public function getXmlContent(): string
    {
        return $this->document->saveXML();
    }

    /**
     * Initialize XPath processor with Darwin Core namespace
     */
    private function initializeXPath(): void
    {
        $this->xpath = new DOMXPath($this->document);
        $this->xpath->registerNamespace('ns', $this->document->documentElement->namespaceURI);
        $this->xpath->registerNamespace('php', 'http://php.net/xpath');
        $this->xpath->registerPhpFunctions();
    }

    /**
     * Validate that the XML has the expected Darwin Core Archive structure
     *
     * @throws InvalidDarwinCoreArchiveException When structure is invalid
     */
    private function validateXmlStructure(): void
    {
        // Check root element is archive
        if ($this->document->documentElement->localName !== 'archive') {
            throw InvalidDarwinCoreArchiveException::invalidRootElement(
                $this->document->documentElement->localName
            );
        }

        // Check namespace
        $expectedNamespace = 'http://rs.tdwg.org/dwc/text/';
        if ($this->document->documentElement->namespaceURI !== $expectedNamespace) {
            throw InvalidDarwinCoreArchiveException::invalidNamespace(
                $this->document->documentElement->namespaceURI ?? 'none',
                $expectedNamespace
            );
        }

        // Validate that either core or extension elements exist
        $coreElements = $this->xpath->query('//ns:archive/ns:core');
        $extensionElements = $this->xpath->query('//ns:archive/ns:extension');

        if ($coreElements->length === 0 && $extensionElements->length === 0) {
            throw InvalidDarwinCoreArchiveException::noDataElements();
        }
    }
}
