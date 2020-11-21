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

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

/**
 * Class RapidUpdateSelectFormRequest
 *
 * @package App\Http\Requests
 */
class RapidUpdateSelectFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '_gbifR' => 'required_without_all:_gbifP,_idbP,_idbR,_rapid',
            '_gbifP' => 'required_without_all:_gbifR,_idbP,_idbR,_rapid',
            '_idbP' => 'required_without_all:_gbifR,_gbifP,_idbR,_rapid',
            '_idbR' => 'required_without_all:_gbifR,_gbifP,_idbP,_rapid',
            '_rapid' => 'required_without_all:_gbifR,_gbifP,_idbP,_idbR'
        ];
    }
}
