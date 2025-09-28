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

use App\Services\DarwinCore\Exceptions\MissingCoreElementException;
use App\Services\DarwinCore\Exceptions\MissingExtensionElementException;
use DOMElement;
use DOMXPath;

/**
 * Darwin Core XPath Service
 *
 * Specialized XPath service for querying Darwin Core Archive meta.xml files.
 * Provides focused methods for finding core and extension elements with proper error handling.
 */
readonly class DarwinCoreXPathService
{
    public function __construct(
        private DOMXPath $xpath,
        public string $filePath = ''
    ) {}

    /**
     * Find the core element in Darwin Core Archive
     *
     * @return DOMElement|null The core element or null if not found
     *
     * @throws \App\Services\DarwinCore\Exceptions\MissingCoreElementException When core element is required but not found
     */
    public function findCoreElement(bool $required = true): ?DOMElement
    {
        $coreElement = $this->xpath->query('//ns:archive/ns:core')->item(0);

        if ($required && $coreElement === null) {
            throw MissingCoreElementException::create($this->filePath);
        }

        // Type cast from DOMNode to DOMElement since XPath query returns DOMNode
        return $coreElement instanceof DOMElement ? $coreElement : null;
    }

    /**
     * Find all extension elements in Darwin Core Archive
     *
     * @return DOMElement[] Array of extension elements
     */
    public function findExtensionElements(): array
    {
        $extensionNodes = $this->xpath->query('//ns:archive/ns:extension');
        $extensions = [];

        foreach ($extensionNodes as $extension) {
            if ($extension instanceof DOMElement) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * Find extension element matching specific row type requirements
     *
     * @param  array  $requiredFields  Required field configuration
     * @param  array  $requiredRowTypes  Valid row types
     * @return DOMElement|null Matching extension element or null
     *
     * @throws \App\Services\DarwinCore\Exceptions\MissingExtensionElementException When no valid extension is found
     */
    public function findValidExtensionElement(array $requiredFields, array $requiredRowTypes): ?DOMElement
    {
        $extensions = $this->findExtensionElements();

        foreach ($extensions as $extension) {
            $matches = $this->countFieldMatches($extension, $requiredFields);

            if ($matches >= count($requiredFields['extension'])) {
                // Validate row type
                if ($this->hasValidRowType($extension, $requiredRowTypes)) {
                    return $extension;
                }
            }
        }

        throw MissingExtensionElementException::noValidExtension($this->filePath, $requiredRowTypes);
    }

    /**
     * Extract field mappings from a core or extension element
     *
     * @param  DOMElement  $element  Core or extension element
     * @return array Field mappings indexed by column index
     */
    public function extractFieldMappings(DOMElement $element): array
    {
        $fields = [];

        foreach ($element->childNodes as $child) {
            if (! ($child instanceof DOMElement) || $child->tagName === 'files') {
                continue;
            }

            if ($child->attributes === null) {
                continue; // Skip nodes without attributes
            }

            $indexAttr = $child->attributes->getNamedItem('index');
            if ($indexAttr === null) {
                continue; // Skip nodes without index
            }

            $index = $indexAttr->nodeValue;

            if ($child->tagName === 'id' || $child->tagName === 'coreid') {
                $fields[$index] = $child->tagName;
            } else {
                $termAttr = $child->attributes->getNamedItem('term');
                if ($termAttr !== null) {
                    $fields[$index] = $termAttr->nodeValue;
                }
            }
        }

        return $fields;
    }

    /**
     * Get attribute value from element
     *
     * @param  DOMElement  $element  Element to query
     * @param  string  $attributeName  Attribute name to get
     * @return string|null Attribute value or null if not found
     */
    public function getAttributeValue(DOMElement $element, string $attributeName): ?string
    {
        $attribute = $element->attributes->getNamedItem($attributeName);

        return $attribute?->nodeValue;
    }

    /**
     * Evaluate XPath expression with context element
     *
     * @param  string  $expression  XPath expression
     * @param  DOMElement  $contextElement  Context element for evaluation
     * @return mixed Evaluation result
     */
    public function evaluate(string $expression, DOMElement $contextElement): mixed
    {
        return $this->xpath->evaluate($expression, $contextElement);
    }

    /**
     * Count field matches for extension validation
     *
     * @param  DOMElement  $extension  Extension element to check
     * @param  array  $requiredFields  Required field configuration
     * @return int Number of matches found
     */
    private function countFieldMatches(DOMElement $extension, array $requiredFields): int
    {
        $matches = 0;

        foreach ($requiredFields['extension'] as $field => $terms) {
            if (count($terms) === 0 && count($extension->getElementsByTagName($field)) > 0) {
                $matches++;

                continue;
            }

            foreach ($terms as $value) {
                $count = (int) $this->xpath->evaluate('count(ns:field[@term=\''.$value.'\'])', $extension);
                if ($count > 0) {
                    $matches++;
                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * Check if extension has valid row type
     *
     * @param  DOMElement  $extension  Extension element to check
     * @param  array  $requiredRowTypes  Valid row types
     * @return bool True if row type is valid
     */
    private function hasValidRowType(DOMElement $extension, array $requiredRowTypes): bool
    {
        $rowType = $this->getAttributeValue($extension, 'rowType');

        if ($rowType === null) {
            return false;
        }

        return in_array(strtolower($rowType), $requiredRowTypes, true);
    }
}
