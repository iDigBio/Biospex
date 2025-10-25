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

use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\DownloadPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Download
 *
 * Represents a downloadable entity associated with specific expeditions and actors.
 * This model is primarily used to manage files that are associated with actors and expeditions
 * in the context of an application.
 *
 * The model includes relationships to link a download to its related `Expedition`,
 * `Actor`, and `GeoLocateDataSource`.
 *
 * Traits:
 * - `HasFactory`: Provides support for generating factories for the model.
 * - `Presentable`: Provides utilities for presenting the model fields conveniently.
 * - `UuidTrait`: Adds functionality to generate UUIDs for the model's primary identifier.
 */
class Download extends BaseEloquentModel
{
    use HasFactory, Presentable, UuidTrait;

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'downloads';

    /**
     * The attributes that are mass assignable.
     *
     * This allows these fields to be assigned en masse when using methods like
     * `create` or `update`.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'expedition_id',
        'actor_id',
        'file',
        'type',
        'updated_at',
    ];

    /**
     * Defines the attributes that should be hidden from JSON/array serialization.
     * This is useful to prevent unintended exposure of sensitive attributes.
     *
     * @var array<string>
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Specifies the route key name for the model, which is `uuid`.
     * This allows model instances to be resolved in routes using the UUID
     * instead of the default `id`.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Defines the presenter class to be used for download-related actions.
     * This can be utilized to manage the presentation logic.
     *
     * @var class-string
     */
    protected string $presenter = DownloadPresenter::class;

    /**
     * Boots the model, initializing any traits or events required for the class.
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();
    }

    /**
     * Defines an inverse one-to-many relationship with the Expedition model.
     *
     * @return BelongsTo The related Expedition model.
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Defines an inverse one-to-many relationship with the Actor model.
     *
     * @return BelongsTo The related Actor model.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Establishes a one-to-one relationship with the GeoLocateDataSource model using the `download_id` as the foreign key and `id` as the local key.
     *
     * @return HasOne The related GeoLocateDataSource model.
     */
    public function geoLocateDataSource(): HasOne
    {
        return $this->hasOne(GeoLocateDataSource::class);
    }

    /**
     * Mutator for the file attribute.
     * Ensures only the filename is stored, not the full path.
     */
    public function setFileAttribute($value)
    {
        // If it's an array (from Filament FileUpload), get the first element
        if (is_array($value)) {
            $value = $value[0] ?? $value;
        }

        // If it's a string path, extract just the filename
        if (is_string($value) && str_contains($value, '/')) {
            $value = basename($value);
        }

        $this->attributes['file'] = $value;
    }
}
