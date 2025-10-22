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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GeoLocateDataSource
 *
 * This class represents a model for GeoLocate data sources.
 * It defines relationships, attributes, and behaviors specific to a GeoLocate data source
 * within the application. The model acts as an interface between the application's
 * business logic and the database.
 */
class GeoLocateDataSource extends BaseEloquentModel
{
    use HasFactory, UuidTrait;

    /**
     * The name of the database table associated with the model.
     */
    protected $table = 'geo_locate_data_sources';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',      // Identifier for the associated project
        'expedition_id',   // Identifier for the associated expedition
        'geo_locate_form_id',          // Identifier for the form related to the data source
        'geo_locate_community_id',    // Identifier for the associated GeoLocate community
        'download_id',     // Identifier for the associated download
        'data_source',     // Type or source of the data
        'data',            // The raw data related to the GeoLocate data source
    ];

    /**
     * The attributes that should be hidden for serialized arrays or JSON.
     */
    protected $hidden = [
        'id', // ID hidden for external representations
    ];

    /**
     * The attributes that should be cast to a specific data type.
     */
    protected $casts = ['data' => 'array'];

    /**
     * Get the route key name for the model.
     *
     * This specifies the key to be used for route-model binding.
     * In this case, the model uses the 'uuid' attribute as its unique identifier.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot function for model events.
     *
     * This is used to initialize properties or behaviors related to the model,
     * such as booting additional traits or handling lifecycle events.
     */
    public static function boot()
    {
        parent::boot();

        // Boot the UUID trait for this model
        static::bootUuidTrait();
    }

    /**
     * Defines a relationship to the associated Project model.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Defines a relationship indicating that the current model belongs to an Expedition.
     *
     * @return BelongsTo The relationship object representing the association between the current model and the Expedition model.
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Establishes a relationship to the associated Download model.
     */
    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }

    /**
     * Defines a relationship to the associated GeoLocateCommunity model.
     *
     * This represents the community associated with the GeoLocate data source.
     */
    public function geoLocateCommunity(): BelongsTo
    {
        return $this->belongsTo(GeoLocateCommunity::class);
    }

    /**
     * Defines a relationship to the associated GeoLocateForm model.
     *
     * This represents the form linked to the GeoLocate data source.
     */
    public function geoLocateForm(): BelongsTo
    {
        return $this->belongsTo(GeoLocateForm::class);
    }
}
