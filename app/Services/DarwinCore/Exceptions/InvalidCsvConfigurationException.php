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
 * Invalid CSV Configuration Exception
 *
 * Thrown when CSV configuration values are invalid or missing in Darwin Core Archive meta.xml.
 */
class InvalidCsvConfigurationException extends Exception implements MetaFileException
{
    /**
     * Create exception for empty file name
     */
    public static function emptyFileName(string $type): self
    {
        return new self(t(':type node missing from xml meta file.', [':type' => ucfirst($type)]));
    }

    /**
     * Create exception for empty delimiter
     */
    public static function emptyDelimiter(string $type): self
    {
        return new self(t('CSV :type delimiter is empty.', [':type' => $type]));
    }

    /**
     * Create exception for empty row type
     */
    public static function emptyRowType(string $type): self
    {
        return new self(t(':type row type is empty or missing.', [':type' => ucfirst($type)]));
    }

    /**
     * Create exception for invalid delimiter character
     */
    public static function invalidDelimiter(string $type, string $delimiter): self
    {
        return new self(t('Invalid CSV :type delimiter: ":delimiter". Must be a single character.', [
            ':type' => $type,
            ':delimiter' => $delimiter,
        ]));
    }

    /**
     * Create exception for invalid enclosure character
     */
    public static function invalidEnclosure(string $type, string $enclosure): self
    {
        return new self(t('Invalid CSV :type enclosure: ":enclosure". Must be a single character.', [
            ':type' => $type,
            ':enclosure' => $enclosure,
        ]));
    }
}
