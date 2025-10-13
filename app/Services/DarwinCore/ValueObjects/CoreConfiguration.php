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

namespace App\Services\DarwinCore\ValueObjects;

use App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException;

/**
 * Core Configuration Value Object for Darwin Core Archive Processing
 *
 * This immutable value object encapsulates configuration settings for processing
 * Darwin Core Archive core CSV files. It ensures data integrity through type safety
 * and validation, managing essential parameters like file paths, delimiters, and
 * row type specifications required for DwC-A parsing.
 *
 * @property-read string $file Path to the core CSV file within the archive
 * @property-read string $delimiter Character used to separate fields in the CSV
 * @property-read string $enclosure Character used to enclose CSV field values
 * @property-read string $rowType Darwin Core term URI identifying the type of data
 */
readonly class CoreConfiguration
{
    /**
     * Creates a new CoreConfiguration instance with validation
     *
     * @throws \App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException When any required configuration value is empty
     */
    public function __construct(
        public string $file,
        public string $delimiter,
        public string $enclosure,
        public string $rowType
    ) {
        $this->validateConfiguration();
    }

    /**
     * Creates CoreConfiguration from meta.xml DOM element attributes
     *
     * Constructs a configuration object from XML attributes, handling special cases
     * like tab delimiters and default field enclosures. This factory method is the
     * primary way to create instances when processing Darwin Core Archive meta.xml files.
     *
     * @param  string  $file  Path to the CSV file within the archive
     * @param  array<string, string>  $attributes  XML element attributes containing fieldsTerminatedBy and fieldsEnclosedBy
     * @param  string  $rowType  Darwin Core term URI identifying the data type
     * @return self New validated configuration instance
     *
     * @throws \App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException
     */
    public static function fromAttributes(string $file, array $attributes, string $rowType): self
    {
        $delimiter = $attributes['fieldsTerminatedBy'] ?? '';
        $enclosure = $attributes['fieldsEnclosedBy'] ?? '';

        // Handle tab delimiter
        if ($delimiter === '\\t') {
            $delimiter = "\t";
        }

        // Default enclosure to double quote if empty
        if ($enclosure === '') {
            $enclosure = '"';
        }

        return new self($file, $delimiter, $enclosure, $rowType);
    }

    /**
     * Validates core configuration values for completeness
     *
     * Ensures all required configuration values (file path, delimiter, row type)
     * are present and non-empty. This validation is crucial for preventing processing
     * errors in the Darwin Core Archive import process.
     *
     * @throws \App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException When any required configuration value is empty
     */
    private function validateConfiguration(): void
    {
        if (empty($this->file)) {
            throw InvalidCsvConfigurationException::emptyFileName('core');
        }

        if (empty($this->delimiter)) {
            throw InvalidCsvConfigurationException::emptyDelimiter('core');
        }

        if (empty($this->rowType)) {
            throw InvalidCsvConfigurationException::emptyRowType('core');
        }
    }

    /**
     * Returns a human-readable representation of the delimiter
     *
     * Converts technical delimiter characters into their readable equivalents,
     * making configuration details more understandable in user interfaces and logs.
     * Special cases like tabs and common delimiters are given descriptive names.
     *
     * @return string Human-readable delimiter representation (e.g., '\t (tab)')
     */
    public function getDisplayDelimiter(): string
    {
        return match ($this->delimiter) {
            "\t" => '\\t (tab)',
            ',' => ', (comma)',
            ';' => '; (semicolon)',
            '|' => '| (pipe)',
            default => $this->delimiter
        };
    }
}
