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

use Auth;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BingoFormRequest
 */
class BingoFormRequest extends FormRequest
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
        $bingoId = isset($this->route('bingo')->id) ? $this->route('bingo')->id : null;

        return [
            'project_id' => 'required',
            'title' => 'required|between:5,20|unique:bingos,title,'.$bingoId,
            'directions' => 'required|between:10,200',
            'contact' => 'required|email',
            'words.*.word' => 'max:30',
        ];
    }

    public function messages()
    {
        return [
            'words.*.word.max' => 'Word is 20 character max',
        ];
    }
}
