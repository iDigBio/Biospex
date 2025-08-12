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

namespace App\Models\Traits;

/**
 * Trait UuidTrait
 *
 * Provides UUID generation functionality for Eloquent models. This trait
 * automatically generates and assigns a UUID to the 'uuid' attribute when
 * a new model instance is being created, ensuring unique identification
 * across all model instances.
 *
 * Key Features:
 * - Automatic UUID generation during model creation
 * - Uses Laravel's Str::uuid() helper for UUID generation
 * - Integrates seamlessly with Eloquent model lifecycle events
 * - Provides consistent UUID handling across all models
 *
 * Usage:
 * Simply use this trait in any Eloquent model and call bootUuidTrait()
 * in the model's boot() method to enable automatic UUID generation.
 */
trait UuidTrait
{
    /**
     * Boot the UUID trait for the model.
     *
     * This method is automatically called when the trait is booted and sets up
     * an event listener for the 'creating' model event. When a new model instance
     * is being created, it automatically generates and assigns a UUID to the
     * 'uuid' attribute using Laravel's Str::uuid() helper method.
     */
    public static function bootUuidTrait(): void
    {
        static::creating(function ($model) {
            $model->uuid = \Str::uuid();
        });
    }
}
