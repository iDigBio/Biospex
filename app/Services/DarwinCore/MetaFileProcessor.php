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

use App\Models\Meta;
use App\Services\DarwinCore\Exceptions\MissingExtensionElementException;
use App\Services\DarwinCore\ValueObjects\CoreConfiguration;
use App\Services\DarwinCore\ValueObjects\ExtensionConfiguration;
use App\Services\DarwinCore\ValueObjects\ProcessedMetaData;
use DOMElement;

/**
 * Meta File Processor
 *
 * Main orchestrator for Darwin Core Archive meta.xml processing.
 * Coordinates XML loading, validation, and data extraction using specialized services.
 */
readonly class MetaFileProcessor
{
    public function __construct(
        private DarwinCoreXmlLoader $xmlLoader,
        private Meta $metaModel,
        private array $dwcRequiredRowTypes,
        private array $dwcRequiredFields
    ) {}

    /**
     * Process Darwin Core Archive meta.xml file
     *
     * @param  string  $filePath  Path to the meta.xml file
     * @return \App\Services\DarwinCore\ValueObjects\ProcessedMetaData Complete processing result
     *
     * @throws \App\Services\DarwinCore\Exceptions\MetaFileException
     */
    public function process(string $filePath): ProcessedMetaData
    {
        // Load and validate XML
        $document = $this->xmlLoader->loadMetaFile($filePath);
        $xpathService = new DarwinCoreXPathService($this->xmlLoader->getXPath(), $filePath);

        // Find and process core element
        $coreElement = $xpathService->findCoreElement();
        $coreConfig = $this->processCoreElement($coreElement, $xpathService);

        // Determine if media is core based on row type
        $mediaIsCore = $coreConfig->isMultimediaCore();

        // Find and process extension element (optional)
        $extensionConfig = $this->processExtensionElement($xpathService);

        // Extract field mappings
        $metaFields = $this->extractMetaFields($coreElement, $extensionConfig?->element ?? null, $xpathService);

        return new ProcessedMetaData(
            core: $coreConfig,
            extension: $extensionConfig?->config,
            metaFields: $metaFields,
            mediaIsCore: $mediaIsCore,
            xmlContent: $this->xmlLoader->getXmlContent()
        );
    }

    /**
     * Save meta-data to database
     */
    public function saveMetaFile(int $projectId, string $xmlContent): void
    {
        $this->metaModel->create([
            'project_id' => $projectId,
            'xml' => $xmlContent,
        ]);
    }

    /**
     * Process core element and extract configuration
     */
    private function processCoreElement(DOMElement $coreElement, DarwinCoreXPathService $xpathService): CoreConfiguration
    {
        $fileName = trim($coreElement->nodeValue);

        $attributes = [];
        foreach ($coreElement->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }

        $rowType = $xpathService->getAttributeValue($coreElement, 'rowType') ?? '';

        return CoreConfiguration::fromAttributes($fileName, $attributes, $rowType);
    }

    /**
     * Process extension element if it exists and matches requirements
     */
    private function processExtensionElement(DarwinCoreXPathService $xpathService): ?\App\Services\DarwinCore\ExtensionConfigurationWrapper
    {
        try {
            $extensionElement = $xpathService->findValidExtensionElement(
                $this->dwcRequiredFields,
                $this->dwcRequiredRowTypes
            );

            $fileName = trim($extensionElement->nodeValue);

            $attributes = [];
            foreach ($extensionElement->attributes as $attr) {
                $attributes[$attr->name] = $attr->value;
            }

            $rowType = $xpathService->getAttributeValue($extensionElement, 'rowType') ?? '';

            // Validate row type
            $this->validateExtensionRowType($rowType, $fileName, $xpathService->filePath);

            $extensionConfig = ExtensionConfiguration::fromAttributes($fileName, $attributes, $rowType);

            return new \App\Services\DarwinCore\ExtensionConfigurationWrapper($extensionConfig, $extensionElement);

        } catch (MissingExtensionElementException $e) {
            // Extension is optional - return null if not found or invalid
            return null;
        }
    }

    /**
     * Extract field mappings from core and extension elements
     */
    private function extractMetaFields(DOMElement $coreElement, ?DOMElement $extensionElement, DarwinCoreXPathService $xpathService): array
    {
        $metaFields = [];

        // Extract core fields
        $metaFields['core'] = $xpathService->extractFieldMappings($coreElement);

        // Extract extension fields if available
        if ($extensionElement !== null) {
            $metaFields['extension'] = $xpathService->extractFieldMappings($extensionElement);
        } else {
            $metaFields['extension'] = [];
        }

        return $metaFields;
    }

    /**
     * Validate extension row type against required types
     *
     * @throws \App\Services\DarwinCore\Exceptions\MissingExtensionElementException
     */
    private function validateExtensionRowType(string $rowType, string $extensionFile, string $filePath): void
    {
        $rowTypeLower = strtolower($rowType);

        if (! in_array($rowTypeLower, $this->dwcRequiredRowTypes, true)) {
            throw MissingExtensionElementException::invalidRowType($filePath, $rowType, $extensionFile);
        }
    }
}

/**
 * Internal wrapper class to hold both extension configuration and DOM element
 */
readonly class ExtensionConfigurationWrapper
{
    public function __construct(
        public ExtensionConfiguration $config,
        public DOMElement $element
    ) {}
}
