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

namespace App\Presenters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Storage;

abstract class Presenter
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return bool
     */
    public function __isset($property)
    {
        return method_exists($this, Str::camel($property));
    }

    /**
     * @return mixed
     */
    public function __get($property)
    {
        $camel_property = Str::camel($property);

        if (method_exists($this, $camel_property)) {
            return $this->{$camel_property}();
        }

        return $this->model->{Str::snake($property)};
    }

    /**
     * @param  \Czim\Paperclip\Attachment\Attachment  $attachment
     * @param  null  $variant
     * @return bool
     */
    public function variantExists($attachment, $variant = null)
    {
        return $attachment->exists() && Storage::disk('public')->exists($attachment->variantPath($variant));
    }
}
