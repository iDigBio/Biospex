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
 * Immutable value object representing CSV configuration for Darwin Core Archive extension file.
 *
 * @param  string  $file  The path or name of the extension file
 * @param  string  $delimiter  The field delimiter character used in the CSV
 * @param  string  $enclosure  The field enclosure character used in the CSV
 * @param  string  $rowType  The Darwin Core term URI defining the type of data in the file
 *
 * @throws \App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException When configuration values are invalid
 */
readonly class ExtensionConfiguration
{
    public function __construct(
        public string $file,
        public string $delimiter,
        public string $enclosure,
        public string $rowType
    ) {
        $this->validateConfiguration();
    }

    /**
     * Create ExtensionConfiguration from DOM element attributes
     *
     * @param  string  $file  The path or name of the extension file
     * @param  array<string, string>  $attributes  The DOM element attributes
     * @param  string  $rowType  The Darwin Core term URI
     * @return self New instance of ExtensionConfiguration
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
     * Validate the configuration values
     *
     * @throws \App\Services\DarwinCore\Exceptions\InvalidCsvConfigurationException When file, delimiter or rowType is empty
     */
    private function validateConfiguration(): void
    {
        if (empty($this->file)) {
            throw InvalidCsvConfigurationException::emptyFileName('extension');
        }

        if (empty($this->delimiter)) {
            throw InvalidCsvConfigurationException::emptyDelimiter('extension');
        }

        if (empty($this->rowType)) {
            throw InvalidCsvConfigurationException::emptyRowType('extension');
        }
    }

    /**
     * Check if this is a multimedia extension
     *
     * @return bool True if rowType contains 'multimedia', false otherwise
     */
    public function isMultimediaExtension(): bool
    {
        return stripos($this->rowType, 'multimedia') !== false;
    }

    /**
     * Check if this is an occurrence extension
     *
     * @return bool True if rowType contains 'occurrence', false otherwise
     */
    public function isOccurrenceExtension(): bool
    {
        return stripos($this->rowType, 'occurrence') !== false;
    }

    /**
     * Get formatted delimiter for display
     *
     * @return string Human-readable representation of the delimiter
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

    /**
     * Get short row type for display (last part of URI)
     *
     * @return string The last segment of the rowType URI
     */
    public function getShortRowType(): string
    {
        $parts = explode('/', $this->rowType);

        return end($parts);
    }
}
