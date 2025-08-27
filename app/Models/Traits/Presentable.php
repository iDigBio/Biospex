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

use App\Exceptions\PresenterException;

/**
 * Trait Presentable
 *
 * Implements the Presenter pattern for Eloquent models. This trait allows
 * models to have associated presenter classes that handle presentation logic,
 * keeping the model focused on business logic while separating view-related
 * formatting and display concerns.
 *
 * Key Features:
 * - Lazy loading of presenter instances for performance
 * - Automatic presenter instantiation based on model configuration
 * - Exception handling for missing or invalid presenter configurations
 * - Singleton pattern for presenter instances per model
 *
 * Usage:
 * Models using this trait should define a protected $presenter property
 * containing the fully qualified class name of their presenter class.
 */
trait Presentable
{
    /**
     * Cached presenter instance for this model.
     * Stores the instantiated presenter to avoid repeated instantiation.
     *
     * @var \App\Presenters\Presenter|null
     */
    protected $presenterInstance;

    /**
     * Get the presenter instance for this model.
     *
     * This method implements lazy loading of the presenter instance. It first
     * checks if a presenter instance already exists and returns it. If not,
     * it attempts to instantiate the presenter class specified in the model's
     * $presenter property. If the presenter property is not set or the class
     * doesn't exist, it throws a PresenterException.
     *
     * @return \App\Presenters\Presenter The presenter instance for this model
     *
     * @throws PresenterException When the presenter property is not set correctly
     */
    public function present()
    {
        if (is_object($this->presenterInstance)) {
            return $this->presenterInstance;
        }

        if (property_exists($this, 'presenter') and class_exists($this->presenter)) {
            return $this->presenterInstance = new $this->presenter($this);
        }

        throw new PresenterException('Property $presenter was not set correctly in '.get_class($this));
    }
}
