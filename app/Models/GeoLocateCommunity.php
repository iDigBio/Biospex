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

use App\Models\Traits\UuidTrait;
use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a GeoLocateCommunity model that interacts with the 'geo_locate_communities' database table.
 * This model includes relationships, casting attributes, and bootable functions to extend its behavior.
 */
class GeoLocateCommunity extends BaseEloquentModel
{
    use Cacheable, HasFactory, UuidTrait;

    /**
     * The name of the database table associated with the model.
     */
    protected $table = 'geo_locate_communities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'name',
        'data',
    ];

    /**
     * Specifies attributes that should be hidden from JSON serialization output.
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Get the relations that should be cached.
     */
    protected function getCacheRelations(): array
    {
        return ['project', 'geoLocateDataSources'];
    }

    /**
     * Boot the model and its traits.
     */
    public static function boot(): void
    {
        parent::boot();

        static::bootUuidTrait();
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the casts for the model's attributes.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /**
     * Defines the relationship indicating that this model belongs to a single Project model.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Establishes a one-to-many relationship with the GeoLocateDataSource model, where multiple geo-location data sources are associated with a specific community.
     */
    public function geoLocateDataSources(): HasMany
    {
        return $this->hasMany(GeoLocateDataSource::class);
    }
}
