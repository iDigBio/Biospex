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

namespace App\Services\DarwinCore\Exceptions;

use Exception;

/**
 * Missing Extension Element Exception
 *
 * Thrown when required <extension> elements are missing or invalid in Darwin Core Archive meta.xml.
 */
class MissingExtensionElementException extends Exception implements MetaFileException
{
    /**
     * Create exception for no valid extension found
     */
    public static function noValidExtension(string $file, array $requiredRowTypes): self
    {
        return new self(t('Unable to determine meta file extension during Darwin Core Archive import. This is typically due to missing required DWC row types. File: :file, Required types: :types', [
            ':file' => $file,
            ':types' => implode(', ', $requiredRowTypes),
        ]));
    }

    /**
     * Create exception for missing extension attributes
     */
    public static function missingAttributes(string $file): self
    {
        return new self(t('Extension element or its attributes are missing from meta.xml file. File: :file', [':file' => $file]));
    }

    /**
     * Create exception for invalid extension row type
     */
    public static function invalidRowType(string $file, string $rowType, string $extensionFile): self
    {
        return new self(t('Row Type mismatch in reading meta xml file. :file, :row_type, :type_file', [
            ':file' => $file,
            ':row_type' => $rowType,
            ':type_file' => $extensionFile,
        ]));
    }
}
