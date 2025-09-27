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
 * Darwin Core Archive Exception
 *
 * Specific exception for Darwin Core Archive processing errors.
 * Provides contextual error messages for different failure scenarios.
 */
class InvalidDarwinCoreArchiveException extends Exception implements MetaFileException
{
    /**
     * Create exception for file not found
     */
    public static function fileNotFound(string $filePath): self
    {
        return new self(t('Darwin Core Archive meta.xml file not found: :file', [':file' => $filePath]));
    }

    /**
     * Create exception for XML parsing failure
     */
    public static function xmlParsingFailed(string $filePath, string $error): self
    {
        return new self(t('Failed to parse Darwin Core Archive meta.xml. File: :file, Error: :error', [
            ':file' => $filePath,
            ':error' => $error,
        ]));
    }

    /**
     * Create exception for invalid root element
     */
    public static function invalidRootElement(string $actualElement): self
    {
        return new self(t('Invalid Darwin Core Archive: root element must be "archive", found ":element"', [
            ':element' => $actualElement,
        ]));
    }

    /**
     * Create exception for invalid namespace
     */
    public static function invalidNamespace(string $actualNamespace, string $expectedNamespace): self
    {
        return new self(t('Invalid Darwin Core Archive namespace. Expected: :expected, Found: :actual', [
            ':expected' => $expectedNamespace,
            ':actual' => $actualNamespace,
        ]));
    }

    /**
     * Create exception when no core or extension elements are found
     */
    public static function noDataElements(): self
    {
        return new self(t('Invalid Darwin Core Archive: must contain at least one <core> or <extension> element'));
    }
}
