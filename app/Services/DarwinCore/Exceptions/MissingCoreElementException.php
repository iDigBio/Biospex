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
 * Missing Core Element Exception
 *
 * Thrown when a required <core> element is missing from Darwin Core Archive meta.xml.
 */
class MissingCoreElementException extends Exception implements MetaFileException
{
    /**
     * Create exception for missing core element
     */
    public static function create(string $file): self
    {
        return new self(t('Core element missing from meta.xml file. Darwin Core Archive must contain a <core> element. File: :file', [':file' => $file]));
    }

    /**
     * Create exception for missing core attributes
     */
    public static function missingAttributes(string $file): self
    {
        return new self(t('Core element or its attributes are missing from meta.xml file. File: :file', [':file' => $file]));
    }
}
