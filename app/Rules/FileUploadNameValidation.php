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

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class FileUploadNameValidation
 */
class FileUploadNameValidation implements Rule
{
    /**
     * messages
     */
    public $message;

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
     * @param  string  $attribute  field name
     * @param  mixed  $value  field value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/[^a-z_\-0-9]/i', $value->getClientOriginalName()) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'File name can only contain alphanumeric, underscores, or dashes';
    }
}
