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

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Header
 */
class Header extends BaseEloquentModel
{
    /**
     * {@inheritDoc}
     */
    protected $table = 'headers';

    /**
     * {@inheritDoc}
     */
    protected $fillable = [
        'project_id',
        'header',
    ];

    /**
     * Project relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Define the header attribute.
     */
    protected function header(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => unserialize($value),
            set: fn ($value) => serialize($value)
        );
    }
}
