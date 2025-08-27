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

use IDigAcademy\AutoCache\Traits\Cacheable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class WeDigBioProject
 */
class WeDigBioProject extends BaseEloquentModel
{
    use Cacheable, HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'wedigbio_projects';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'panoptes_project_id',
        'panoptes_workflow_id',
        'subject_sets',
    ];

    // TODO: Is this used anywhere?
    protected function subjectSets(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value)
        );
    }
}
