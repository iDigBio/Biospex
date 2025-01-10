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
 * Class GroupFormRequest
 */
class GroupFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $groupId = isset($this->route('group')->id) ? $this->route('group')->id : null;

        return [
            'title' => 'required|min:4|max:32|not_in:'.config('config.admin.group').'|unique:groups,title,'.$groupId,
            'user_id' => 'required',
        ];
    }

    /**
     * Alter group form input before validation.
     */
    public function alterInput(): array
    {
        $input = $this->all();
        $input['title'] = $this->route('groups') === config('config.admin.group_id') ? config('config.admin.group_id') : trim($input['title']);
        $this->replace($input);

        return $this->all();
    }
}
