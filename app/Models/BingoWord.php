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
 * Class BingoWord
 *
 * @package App\Models
 */
class BingoWord extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'bingo_words';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'bingo_id',
        'word',
        'definition',
    ];

    /**
     * Bingo relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bingo()
    {
        return $this->belongsTo(Bingo::class);
    }

    /**
     * Define the ip attribute.
     *
     * @return Attribute
     */
    protected function ip()
    {
        return Attribute::make(
            get: fn($value) => inet_ntop($value),
            set: fn($value) => inet_pton($value)
        );
    }
}
