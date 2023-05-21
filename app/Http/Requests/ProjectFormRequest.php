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

use App\Rules\FileUploadNameValidation;
use App\Rules\ResourceDownloadValidation;
use Illuminate\Support\Facades\Auth;
use App\Rules\ResourceNameValidation;

/**
 * Class ProjectFormRequest
 *
 * @package App\Http\Requests
 */
class ProjectFormRequest extends Request
{
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'group_id'                => 'required|integer|min:1',
            'title'                   => 'required|between:6,140|unique:projects,title,'.$this->route('projects'),
            'contact'                 => 'required',
            'contact_email'           => 'required|min:4|max:32|email',
            'contact_title'           => 'required',
            'description_short'       => 'required|between:6,140',
            'description_long'        => 'required',
            'keywords'                => 'required',
            'workflow_id'             => 'required',
            'organization_website'    => 'nullable|url',
            'blog_url'                => 'nullable|url',
            'facebook'                => 'nullable|url',
            'twitter'                 => 'nullable|url',
            'logo'                    => [
                'image',
                new FileUploadNameValidation(),
            ],
            'resources.*.type'        => [new ResourceDownloadValidation()],
            'resources.*.name'        => ['required_with:resources.*.type', new ResourceNameValidation()],
            'resources.*.description' => 'required_with:resources.*.type',
            'resources.*.download'    => 'mimes:txt,doc,csv,pdf',
        ];
    }

    /**
     * Alter group form input before validation.
     *
     * @return array
     */
    public function alterInput()
    {
        $input = $this->all();
        $input['title'] = trim($input['title']);
        $this->replace($input);

        return $this->all();
    }

    public function messages()
    {
        return [
            'resources.*.name.required_with'        => 'Required when Type selected',
            'resources.*.description.required_with' => 'Required when Type selected',
            'resources.*.download.required_if'      => 'Required when Type selected',
            'resources.*.download.mimes'            => 'Accepted files: txt,doc,csv,pdf',
        ];
    }
}
