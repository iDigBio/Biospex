<?php

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid;

trait UuidTrait
{
    /**
     * Boot the Uuid trait for the model.
     *
     * @return void
     */
    public static function bootUuidTrait()
    {
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->__toString();
        });
    }

    /**
     * Set uuid for binary storage.
     */
    public function setUuidAttribute($value)
    {
        $this->attributes['uuid'] = pack('H*', str_replace('-', '', $value));
    }

    /**
     * Return uuid in normal format.
     *
     * @return string
     */
    public function getUuidAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $uuid = bin2hex($value);

        return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20);
    }
}
/**
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
