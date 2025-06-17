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

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Request;

/**
 * Class ResourceDownloadValidation
 */
class ResourceDownloadValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        /**
        [resources] => Array
        (
        [1] => Array
        (
        [download] => Illuminate\Http\UploadedFile Object
        (
        [test:Symfony\Component\HttpFoundation\File\UploadedFile:private] =>
        [originalName:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 21_Cryptos Magazine January 2018-3.pdf
        [mimeType:Symfony\Component\HttpFoundation\File\UploadedFile:private] => application/pdf
        [size:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 5375683
        [error:Symfony\Component\HttpFoundation\File\UploadedFile:private] => 0
        [hashName:protected] =>
        [pathName:SplFileInfo:private] => /tmp/php1hX8bG
        [fileName:SplFileInfo:private] => php1hX8bG
        )

        )

        )
         */
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parts = explode('.', $attribute);
        $resources = Request::get('resources');
        $fileUpload = Request::hasFile('resources.'.$parts[1].'.download');

        if ($resources[$parts[1]]['type'] !== 'File Download' ||
            $resources[$parts[1]]['type'] === 'delete' ||
            ! $fileUpload) {
            return true;
        }

        $file = Request::file('resources.'.$parts[1].'.download');

        return preg_match('/^[\w\-.]+$/', $file->getClientOriginalName()) === 1;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'File required and name can only contain alphanumeric, dash, or underscore.';
    }
}
