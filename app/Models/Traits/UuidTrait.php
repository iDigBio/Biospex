<?php namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Casts\Attribute;
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
            //$model->uuid = Uuid::uuid4()->__toString();
            $model->uuid = Uuid::uuid4()->getBytes();
        });
    }

    /**
     * Define the uuid attribute.
     *
     * @return Attribute
     */
    protected function uuid(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Uuid::fromBytes($value)->toString(),
            set: fn($value) => Uuid::uuid4()->getBytes()
        );
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


