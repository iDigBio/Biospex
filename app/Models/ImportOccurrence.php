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

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Temporary MongoDB model for Darwin Core import occurrence data.
 * This model is used for large datasets to avoid memory issues.
 * Data is stored temporarily and cleared after processing.
 */
class ImportOccurrence extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mongodb';

    /**
     * The collection associated with the model.
     */
    protected $collection = 'import_occurrences';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'occurrence_id',
        'data',
        'project_id',
        'import_session_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'project_id' => 'integer',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Find occurrence data by occurrence ID.
     */
    public static function findByOccurrenceId(string $occurrenceId, string $importSessionId): ?array
    {
        $record = static::where('occurrence_id', $occurrenceId)
            ->where('import_session_id', $importSessionId)
            ->first();

        return $record ? $record->data : null;
    }

    /**
     * Clear all data for a specific import session.
     */
    public static function clearImportSession(string $importSessionId): void
    {
        static::where('import_session_id', $importSessionId)->delete();
    }

    /**
     * Clear all import occurrence data.
     */
    public static function clearAll(): void
    {
        static::truncate();
    }

    /**
     * Count records for an import session.
     */
    public static function countForSession(string $importSessionId): int
    {
        return static::where('import_session_id', $importSessionId)->count();
    }
}
