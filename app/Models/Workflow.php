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

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Workflow
 */
class Workflow extends BaseEloquentModel
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $table = 'workflows';

    /**
     * {@inheritDoc}
     */
    protected $fillable = ['title', 'enabled'];

    /**
     * Actors relation.
     */
    public function actors(): mixed
    {
        return $this->belongsToMany(Actor::class)->withPivot('order')->orderByPivot('order');
    }

    /**
     * Expedition Relation.
     */
    public function expedition(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expedition::class);
    }
}
