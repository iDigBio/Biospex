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
 * Class ImportFormRequest
 */
class ImportFormRequest extends Request
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
        switch ($this->get('method')) {
            case 'darwinCoreFileImport':
                return ['core' => 'required|mimes:zip'];
            case 'darwinCoreUrlImport':
                return ['core-url' => 'required|url'];
            case 'recordSetImport':
                return ['recordset' => 'required|alpha_dash'];
            default:break;
        }

        return [];
    }

    public function alterInput()
    {
        $input = $this->all();

        // Alter record set if available
        if ($this->input('recordset') !== null) {
            $input['recordset'] = strstr($this->input('recordset'), '/') ?
                trim(strrchr($this->input('recordset'), '/'), '/') : trim($this->input('recordset'));
        } else {
            $input['recordset'] = $this->input('recordset');
        }

        $this->replace($input);

        return $this->all();
    }
}
