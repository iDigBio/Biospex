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

use App\Models\Traits\Presentable;
use App\Models\Traits\UuidTrait;
use App\Presenters\DownloadPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * The presenter class for this model.
     *
     * This is used for preparing model data for proper display in the UI or other contexts.
     *
     * @var string
     */
    protected $presenter = DownloadPresenter::class;

    /**
     * Boot the model and configure its events.
     *
     * This also initializes the `UuidTrait` functionality, ensuring that UUIDs
     * are automatically generated for `Download` instances.
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootUuidTrait();
    }

    /**
     * Defines a relationship with the `Expedition` model.
     *
     * This associates a downloadable object with a specific expedition.
     * For example, files related to expeditions can be tracked or retrieved
     * via this relationship.
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Defines a relationship with the `Actor` model.
     *
     * This links a downloadable object to the specific actor (user or system entity) responsible for the file.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }

    /**
     * Defines a relationship with the `GeoLocateDataSource` model.
     *
     * This relates a downloadable object to geolocation data associated with the file.
     */
    public function geoLocateDataSource(): BelongsTo
    {
        return $this->belongsTo(GeoLocateDataSource::class, 'download_id', 'id');
    }
}
