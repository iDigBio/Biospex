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
 * Class AssetDownloadPathValidation
 */
class AssetDownloadPathValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $assets = Request::get('assets');

        // If no assets or this asset index doesn't exist, pass validation
        if (! $assets || ! isset($assets[$parts[1]])) {
            return true;
        }

        $asset = $assets[$parts[1]];

        // Only validate if type is 'File Download' and not marked for deletion
        if ($asset['type'] !== 'File Download' || $asset['type'] === 'delete') {
            return true;
        }

        // For edit forms, if there's already a download_path (file already uploaded), it's valid
        if (! empty($value)) {
            return true;
        }

        // If no existing path and it's a File Download type, check for file upload
        $fileUpload = Request::hasFile('assets.'.$parts[1].'.download');

        if ($fileUpload) {
            $file = Request::file('assets.'.$parts[1].'.download');
            $allowedMimes = ['txt', 'doc', 'csv', 'pdf'];

            return in_array($file->getClientOriginalExtension(), $allowedMimes);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Accepted files: txt,doc,csv,pdf';
    }
}
