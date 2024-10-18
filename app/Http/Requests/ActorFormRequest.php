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

/**
 * Class ActorFormRequest
 */
class ActorFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|between:3,60|unique:actors,title,'.$this->route('actors'),
            'url' => 'required|active_url|max:255',
            'class' => 'required|between:3,30|unique:actors,class,'.$this->route('actors'),
            'contacts.*.email' => 'email',
        ];
    }

    public function messages()
    {
        return [
            'contacts.*.email' => 'Please enter valid email addresses',
        ];
    }

    public function alterInput()
    {
        $input = $this->all();
        $input['private'] = $this->input('private') === null ? 0 : 1;
        $this->replace($input);

        return $this->all();
    }
}
