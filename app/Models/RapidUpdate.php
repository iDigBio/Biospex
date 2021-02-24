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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RapidUpdate extends BaseEloquentModel
{
    /**
     * @inheritDoc
     */
    protected $table = 'rapid_updates';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'header_id',
        'user_id',
        'file_name',
        'fields_updated',
        'updated_records',
    ];

    /**
     * Belongs to relation with Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Has one relation to RapidHeader.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function headers(): HasOne
    {
        return $this->hasOne(RapidHeader::class);
    }

    /**
     * Mutator for fields_updated column.
     *
     * @param $value
     * @return mixed
     */
    public function getFieldsUpdatedAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Setter for fields_updated column.
     *
     * @param $value
     */
    public function setFieldsUpdatedAttribute($value)
    {
        $this->attributes['fields_updated'] = serialize($value);
    }
}
