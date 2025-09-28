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

/**
 * Processed Meta Data
 *
 * Immutable value object representing the complete result of Darwin Core Archive meta.xml processing.
 * Contains all configuration data needed for CSV import processing.
 */
readonly class ProcessedMetaData
{
    public function __construct(
        public CoreConfiguration $core,
        public ?ExtensionConfiguration $extension,
        public array $metaFields,
        public bool $mediaIsCore,
        public string $xmlContent
    ) {}

    /**
     * Get core file configuration
     */
    public function getCoreFile(): string
    {
        return $this->core->file;
    }

    /**
     * Get core CSV delimiter
     */
    public function getCoreDelimiter(): string
    {
        return $this->core->delimiter;
    }

    /**
     * Get core CSV enclosure character
     */
    public function getCoreEnclosure(): string
    {
        return $this->core->enclosure;
    }

    /**
     * Get extension file configuration (if exists)
     */
    public function getExtensionFile(): ?string
    {
        return $this->extension?->file;
    }

    /**
     * Get extension CSV delimiter (if exists)
     */
    public function getExtDelimiter(): ?string
    {
        return $this->extension?->delimiter;
    }

    /**
     * Get extension CSV enclosure character (if exists)
     */
    public function getExtEnclosure(): ?string
    {
        return $this->extension?->enclosure;
    }

    /**
     * Get field mappings for core
     */
    public function getCoreMetaFields(): array
    {
        return $this->metaFields['core'] ?? [];
    }

    /**
     * Get field mappings for extension
     */
    public function getExtensionMetaFields(): array
    {
        return $this->metaFields['extension'] ?? [];
    }

    /**
     * Check if media records are in the core file
     */
    public function isMediaCore(): bool
    {
        return $this->mediaIsCore;
    }

    /**
     * Check if extension configuration exists
     */
    public function hasExtension(): bool
    {
        return $this->extension !== null;
    }

    /**
     * Get the core row type
     */
    public function getCoreRowType(): string
    {
        return $this->core->rowType;
    }

    /**
     * Get the extension row type (if exists)
     */
    public function getExtensionRowType(): ?string
    {
        return $this->extension?->rowType;
    }
}
