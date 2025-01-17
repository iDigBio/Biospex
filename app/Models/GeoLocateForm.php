<?php

/*
 * Copyright (C) 2015  Biospex
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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Models;

use App\Models\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class GeoLocateForm
 *
 * Represents a form used to record geographic location data. This model class
 * is associated with the `geo_locate_forms` database table and includes
 * relationships with associated groups, expeditions, and data sources.
 *
 * Key Features:
 * - Supports UUID for unique identification.
 * - Provides attribute-level casts for custom data formats.
 * - Tracks form-level data like `group_id`, `fields`, and `hash`.
 * - Maintains relationships to `Group`, `Expedition`, and `GeoLocateDataSource`.
 */
class GeoLocateForm extends BaseEloquentModel
{
    use HasFactory, UuidTrait;

    /**
     * Table associated with this model.
     *
     * @var string
     */
    protected $table = 'geo_locate_forms';

    /**
     * Attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'group_id',    // Reference to the related Group model
        'name',        // Name of the form
        'hash',        // A unique hash for the form (e.g., for validation)
        'fields',      // Form field data in array format
    ];

    /**
     * Attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'fields' => 'array',            // Casts the `fields` attribute to an array
            'created_at' => 'datetime:Y-m-d', // Formats the `created_at` field as 'Y-m-d'
        ];
    }

    /**
     * Boot method for the model.
     *
     * Automatically initializes the UUID trait functionality during model boot.
     */
    public static function boot(): void
    {
        parent::boot();

        static::bootUuidTrait(); // Enables UUID trait upon model initialization
    }

    /**
     * Defines the route key name for the model.
     *
     * This ensures that the UUID is used as a unique identifier when
     * resolving model instances via route model bindings.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Defines a "belongs to" relationship with the Group model.
     *
     * A GeoLocateForm belongs to a single Group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Defines a "has many" relationship with the Expedition model.
     *
     * A GeoLocateForm can have multiple associated Expeditions.
     */
    public function expeditions(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Expedition::class, GeoLocateDataSource::class,
            'geo_locate_form_id',
            'id',
            'id',
            'expedition_id'
        );
    }

    /**
     * Defines a "has many" relationship with the GeoLocateDataSource model.
     *
     * A GeoLocateForm can be associated with multiple data sources
     * via this relationship.
     */
    public function geoLocateDataSources(): HasMany
    {
        return $this->hasMany(GeoLocateDataSource::class);
    }
}
